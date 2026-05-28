<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;
use Psr\Log\LoggerInterface;

/**
 * Handles closing a zaak (setting eindstatus). ZRC-007/ZRC-021.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
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
            throw new \RuntimeException('ZGWZaakCloseService requires the OpenRegister app to be installed and enabled.');
        }

        $this->objectService = $objectService;
    }//end __construct()

    /**
     * Close a zaak when eindstatus is set.
     *
     * Throws CustomValidationException when a required field (resultaat, zaaktype, etc.)
     * is missing or malformed so that the caller can surface the error rather than silently
     * leaving the zaak without required archive metadata (Archiefwet) — fixes #273.
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
        $this->assertGebruiksrechten($zaakArray);

        // Guard: zaak must have a resultaat before it can be closed with archive metadata.
        if (empty($zaakArray['resultaat']) === true) {
            throw new CustomValidationException(
                'Zaak heeft geen resultaat',
                [['name' => 'resultaat', 'code' => 'required', 'reason' => 'Een zaak moet een resultaat hebben voordat hij gesloten kan worden']]
            );
        }

        try {
            $zaakArray['einddatum'] = (new DateTime($statusArray['datumStatusGezet']))->format("Y-m-d");
        } catch (\Exception $e) {
            throw new CustomValidationException(
                'Ongeldige datumStatusGezet',
                [['name' => 'datumStatusGezet', 'code' => 'invalid', 'reason' => 'datumStatusGezet bevat geen geldige ISO 8601 datum: '.$e->getMessage()]]
            );
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
        return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), extend: $extend);
    }//end find()
}//end class
