<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;

/**
 * Handles closing a zaak (setting eindstatus). ZRC-007/ZRC-021.
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
     */
    public function closeZaak(ObjectEntity $status): void
    {
        $sa = $status->jsonSerialize();

        if ($this->isEindStatus($sa) === false) {
            return;
        }

        $zaak = $this->find($sa['zaak'], ['zaakinformatieobjecten', 'zaakinformatieobjecten.informatieobject']);
        $za   = $zaak->jsonSerialize();
        $this->assertGebruiksrechten($za);

        $za['einddatum'] = (new DateTime($sa['datumStatusGezet']))->format("Y-m-d");
        $rt = $this->find($this->find($za['resultaat'])->jsonSerialize()['resultaattype'])->jsonSerialize();
        $za['archiefnominatie']  = $rt['archiefnominatie'];
        $za['archiefactiedatum'] = $this->archiveService->calculateArchiveDate(
            $rt['brondatumArchiefprocedure']['afleidingswijze'] ?? null,
            $za,
                $rt,
                $this->registry->getBrcRegister(),
                $this->registry->getBesluitSchema()
        );

        $this->objectService->clearCurrents();
        $zaak->setObject($za);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }//end closeZaak()

    /**
     * Check if status is eindstatus for its zaaktype.
     */
    public function isEindStatus(array $sa): bool
    {
        $st  = $this->find($sa['statustype'], ['_extend.zaaktype' => 'zaaktype', '_extend.statustypen' => 'zaaktype.statustypen']);
        $d   = $st->jsonSerialize();
        $max = max(array_map(fn(array $s) => $s['volgnummer'], $d['_extend']['zaaktype']['_extend']['statustypen']));
        return $d['volgnummer'] === $max;
    }//end isEindStatus()

    private function assertGebruiksrechten(array $za): void
    {
        $bad = array_filter($za['zaakinformatieobjecten'], fn(array $z) => count($z['informatieobject']['gebruiksrechten']) === 0 && $z['informatieobject']['indicatieGebruiksrecht'] === null);
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
