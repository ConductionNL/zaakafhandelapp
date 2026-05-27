<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;

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
    ) {
        $this->objectService = $mapperService->getOpenRegisters();
    }//end __construct()

    /**
     * Close a zaak when eindstatus is set.
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

        $zaakArray['einddatum'] = (new DateTime($statusArray['datumStatusGezet']))->format("Y-m-d");
        $resultaattype          = $this->find($this->find($zaakArray['resultaat'])->jsonSerialize()['resultaattype'])->jsonSerialize();
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
        $statustype = $this->find($statusArray['statustype'], ['_extend.zaaktype' => 'zaaktype', '_extend.statustypen' => 'zaaktype.statustypen']);
        $statusData = $statustype->jsonSerialize();
        $max        = max(array_map(fn(array $statusItem) => $statusItem['volgnummer'], $statusData['_extend']['zaaktype']['_extend']['statustypen']));
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
