<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Handles closing a zaak (setting eindstatus). ZRC-007/ZRC-021.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZGWZaakCloseService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    public function __construct(
        ObjectMapperService $mapperService,
        private ZGWArchiveDateService $archiveService,
        private ZGWRegistryService $registry,
        private LoggerInterface $logger,
    ) {
        $objectService = $mapperService->getOpenRegisters();
        if ($objectService === null) {
            throw new RuntimeException('ZGWZaakCloseService requires the OpenRegister app to be installed and enabled.');
        }

        $this->objectService = $objectService;
    }//end __construct()

    /**
     * Validate that all prerequisites for closing a zaak are met, without making any mutations.
     *
     * Called from handleObjectCreating (before the status record is persisted) so that a failed
     * prerequisite check aborts the status write entirely, preventing an inconsistent state where
     * the eindstatus is persisted but the zaak's archive metadata is never set (H3).
     *
     * Checks performed:
     * - Is the status an eindstatus for its zaaktype?
     * - Does the associated zaak have a resultaat?
     * - Are all informatieobjecten linked to the zaak properly tagged with gebruiksrechten?
     * - Is datumStatusGezet a valid ISO 8601 date string?
     *
     * If any check fails, a CustomValidationException is thrown and the status write is rejected
     * before any data is committed.
     *
     * @param ObjectEntity $status The status entity that is about to be created.
     *
     * @return void
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-002
     */
    public function validateClosePrerequisites(ObjectEntity $status): void
    {
        $statusArray = $status->jsonSerialize();

        if ($this->isEindStatus($statusArray) === false) {
            // Not an eindstatus — no prerequisites to check.
            return;
        }

        // Load the zaak with its informatieobjecten so gebruiksrechten can be inspected.
        $zaakArray = $this->find($statusArray['zaak'], ['zaakinformatieobjecten', 'zaakinformatieobjecten.informatieobject'])->jsonSerialize();

        // Guard: zaak must have a resultaat.
        if (empty($zaakArray['resultaat']) === true) {
            throw new CustomValidationException(
                'Zaak heeft geen resultaat',
                [['name' => 'resultaat', 'code' => 'required', 'reason' => 'Een zaak moet een resultaat hebben voordat hij gesloten kan worden']]
            );
        }

        // Guard: all informatieobjecten must have gebruiksrechten configured.
        $this->assertGebruiksrechten($zaakArray);

        // Guard: datumStatusGezet must be a valid ISO 8601 date.
        try {
            (new DateTime($statusArray['datumStatusGezet']))->format("Y-m-d");
        } catch (\Exception $e) {
            throw new CustomValidationException(
                'Ongeldige datumStatusGezet',
                [['name' => 'datumStatusGezet', 'code' => 'invalid', 'reason' => 'datumStatusGezet bevat geen geldige ISO 8601 datum: '.$e->getMessage()]]
            );
        }
    }//end validateClosePrerequisites()

    /**
     * Close a zaak when eindstatus is set.
     *
     * Preconditions are enforced by validateClosePrerequisites() in handleObjectCreating
     * before the status record is persisted. This method runs in handleObjectCreated (after
     * persist) and only performs the zaak mutations — it does NOT re-validate prerequisites
     * to avoid double-loading the zaak and its informatieobjecten.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-002
     */
    public function closeZaak(ObjectEntity $status): void
    {
        $statusArray = $status->jsonSerialize();

        if ($this->isEindStatus($statusArray) === false) {
            return;
        }

        $zaak      = $this->find($statusArray['zaak'], ['zaakinformatieobjecten', 'zaakinformatieobjecten.informatieobject']);
        $zaakArray = $zaak->jsonSerialize();

        try {
            $zaakArray['einddatum'] = (new DateTime($statusArray['datumStatusGezet']))->format("Y-m-d");
        } catch (\Exception $e) {
            // datumStatusGezet was already validated in validateClosePrerequisites; log and skip.
            $this->logger->error('ZaakAfhandelApp: closeZaak unexpected date error', ['exception' => $e->getMessage()]);
            return;
        }

        try {
            $resultaatRecord = $this->find($zaakArray['resultaat']);
            $resultaattype   = $this->find($resultaatRecord->jsonSerialize()['resultaattype'])->jsonSerialize();
        } catch (\Exception $e) {
            $this->logger->error('ZaakAfhandelApp: closeZaak cannot resolve resultaattype', ['exception' => $e->getMessage()]);
            throw new CustomValidationException(
                'Resultaattype niet gevonden',
                [['name' => 'resultaattype', 'code' => 'not-found', 'reason' => 'Het resultaattype kon niet worden opgehaald: '.$e->getMessage()]]
            );
        }

        $zaakArray['archiefnominatie']  = $resultaattype['archiefnominatie'];
        $zaakArray['archiefactiedatum'] = $this->archiveService->calculateArchiveDate(
            $resultaattype['brondatumArchiefprocedure']['afleidingswijze'] ?? null,
            $zaakArray,
            $resultaattype,
            $this->registry->getBrcRegister(),
            $this->registry->getBesluitSchema()
        );

        $this->objectService->clearCurrents();
        $zaak->setObject($zaakArray);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }//end closeZaak()

    /**
     * Check if status is eindstatus for its zaaktype.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-002
     */
    public function isEindStatus(array $statusArray): bool
    {
        $statustype  = $this->find($statusArray['statustype'], ['_extend.zaaktype' => 'zaaktype', '_extend.statustypen' => 'zaaktype.statustypen']);
        $statusData  = $statustype->jsonSerialize();
        $statustypen = $statusData['_extend']['zaaktype']['_extend']['statustypen'] ?? [];

        if (empty($statustypen) === true) {
            throw new CustomValidationException(
                'Zaaktype heeft geen statustypen',
                [['name' => 'statustype', 'code' => 'no-statustypen', 'reason' => 'Het zaaktype heeft geen statustypen; kan niet bepalen of dit een eindstatus is.']]
            );
        }

        $max = max(array_map(fn(array $statusItem) => $statusItem['volgnummer'], $statustypen));
        return $statusData['volgnummer'] === $max;
    }//end isEindStatus()

    private function assertGebruiksrechten(array $zaakArray): void
    {
        $bad = array_filter($zaakArray['zaakinformatieobjecten'], fn(array $zio) => count($zio['informatieobject']['gebruiksrechten']) === 0 && $zio['informatieobject']['indicatieGebruiksrecht'] === null);
        if (count($bad) > 0) {
            throw new CustomValidationException("Indicatiegebruiksrecht niet geset", [['name' => 'nonFieldErrors', 'code' => 'indicatiegebruiksrecht-unset', 'reason' => 'Alle informatieobjecten moeten een gebruiksrecht hebben.']]);
        }
    }//end assertGebruiksrechten()

    private function find(string $url, array $extend=[]): ObjectEntity
    {
        $this->objectService->clearCurrents();
        return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), _extend: $extend);
    }//end find()
}//end class
