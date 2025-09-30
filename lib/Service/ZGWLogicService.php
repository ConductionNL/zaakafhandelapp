<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateInterval;
use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\ZaakAfhandelApp\Service\ObjectService;

class ZGWLogicService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    private array $registers;
    private array $schemas;

    /**
     * @param ObjectService $objectService
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(
        ObjectService $objectService
    )
    {
        $this->objectService = $objectService->getOpenRegisters();

        $this->registers = [
            'drc' => 'documenten',
            'brc' => 'besluiten',
            'zrc' => 'zaken',
            'ztc' => 'catalogi',
        ];

        $this->schemas = [
            'zio'     => 'zaakinformatieobject',
            'bio'     => 'besluitinformatieobject',
            'oio'     => 'objectinformatieobject',
            'besluit' => 'besluit',

        ];
    }

    public function getDrcRegister(): string
    {
        return $this->registers['drc'];
    }

    public function getBrcRegister(): string
    {
        return $this->registers['brc'];
    }

    public function getZrcRegister(): string
    {
        return $this->registers['zrc'];
    }

    public function getZtcRegister(): string
    {
        return $this->registers['ztc'];
    }

    public function getZioSchema(): string
    {
        return $this->schemas['zio'];
    }

    public function getBioSchema(): string
    {
        return $this->schemas['bio'];
    }

    public function getOioSchema(): string
    {
        return $this->schemas['oio'];
    }

    public function getBesluitSchema(): string
    {
        return $this->schemas['besluit'];
    }

    /**
     * When eindstatus is set on a zaak, close the zaak by setting appropriate values
     * ZRC-007 and ZRC-021
     *
     * @param string|int $statusId
     * @return void
     */
    public function closeZaak(string|int $statusId): void
    {
        $status = $this->objectService->find($statusId);
        $this->objectService->clearCurrents();

        $statusArray = $status->jsonSerialize();
        // Validate endStatus
        $statusType = $this->objectService->find($statusArray['statustype']);
        $this->objectService->clearCurrents();

        if($statusType->jsonSerialize()['isEindstatus'] === false) {
            return;
        }

        // Get zaak
        $zaak = $this->objectService->find($statusArray['zaak']);
        $this->objectService->clearCurrents();

        $zaakArray = $zaak->jsonSerialize();

        //Set fields for closed zaak
        $zaakArray['einddatum'] = $statusArray['datumStatusGezet'];

        $resultaat = $this->objectService->find($zaakArray['resultaat']);
        $this->objectService->clearCurrents();

        $resultaatType = $this->objectService->find($resultaat->jsonSerialize()['resultaatType']);

        $resultaattypeArray = $resultaatType->jsonSerialize();

        $zaakArray['archiefnominatie'] = $resultaattypeArray['archiefnominatie'];

        $afleidingswijze = $resultaattypeArray['brondatumArchiefprocedure']['afleidingswijze'] ?? null;
        switch($afleidingswijze) {
            case 'afgehandeld':
                $zaakArray['archiefactiedatum'] = $zaakArray['einddatum'];
                break;
            case 'hoofdzaak':
                $hoofdzaak = $this->objectService->find($zaakArray['hoofdzaak']);
                $this->objectService->clearCurrents();

                $zaak['archiefactiedatum'] = $hoofdzaak->jsonSerialize()['einddatum'];
                break;
            case 'eigenschap':
                $eigenschap = $resultaattypeArray['brondatumArchiefprocedure']['datumkenmerk'] ?? null;
                $eigenschappen = $this->objectService->findAll(['ids' => $zaak['eigenschappen']]);
                $this->objectService->clearCurrents();
                $eigenschapObjects = array_filter($eigenschappen, function (ObjectEntity $eigenschapObject) use ($eigenschap) {return $eigenschapObject->jsonSerialize()['naam'] === $eigenschap;});
                $eigenschapObject = array_shift($eigenschapObjects);

                $zaak['archiefactiedatum'] = $eigenschapObject->jsonSerialize()['waarde'];
                break;
            case 'ander_datumkenmerk':
                $zaakArray['archiefactiedatum'] = null;
                break;
            case 'termijn':
                $zaakArray['archiefactiedatum'] = new DateTime($zaakArray['einddatum'])->add(new DateInterval($resultaattypeArray['brondatumArchiefprocedure']['procestermijn']));
                break;
            case 'ingangsdatum_besluit':
            case 'vervaldatum_besluit':
                // Can we please stop breaking performance?
                // fetch besluiten
                $besluiten = $this->objectService->findAll(['filters' => ['zaak' => $zaakArray['url'], 'register' => $this->getBrcRegister(), 'schema'=> $this->getBesluitSchema()]]);
                $this->objectService->clearCurrents();

                if ($afleidingswijze === 'ingangsdatum_besluit') {
                    $data = array_map(function (ObjectEntity $besluit) {
                        $besluit->jsonSerialize()['ingangsdatum'];
                    }, $besluiten);
                } else {
                    $data = array_map(function (ObjectEntity $besluit) {
                        $besluit->jsonSerialize()['vervaldatum'];
                    }, $besluiten);
                }

                // get max ingangsdatum or max vervaldatum
                $zaakArray['archiefactiedatum'] = max($data);
                break;
            case 'gerelateerde_zaak':
            case 'zaakobject':
            default:
                break;

        }


        // Save zaak
        $this->objectService->saveObject($zaak, $zaakArray);
    }

    /**
     * When a status is set that is not an eindstatus, unset appropriate values for closed zaak
     * ZRC-008
     *
     * @param string|int $statusId
     * @return void
     */
    public function reopenZaak(string|int $statusId): void
    {
        $status = $this->objectService->find($statusId);
        $this->objectService->clearCurrents();

        $statusArray = $status->jsonSerialize();

        // Validate not endStatus
        $statusType = $this->objectService->find($statusArray['statustype']);
        $this->objectService->clearCurrents();

        if($statusType->jsonSerialize()['isEindstatus'] === true) {
            return;
        }

        // Get zaak
        $zaak = $this->objectService->find($statusArray['zaak']);
        $this->objectService->clearCurrents();

        $zaakArray = $zaak->jsonSerialize();

        // Unset fields for closed zaak
        $zaakArray['einddatum'] = null;
        $zaakArray['archiefactiedatum'] = null;
        $zaakArray['archiefnominatie'] = null;

        // Save zaak
        $this->objectService->saveObject($zaak, $zaakArray);
    }

    /**
     * Delete objects that are dependent on a zaak when the zaak is deleted
     * ZRC-023
     *
     * @param string|int $zaakId
     * @return void
     */
    public function deleteZaak(string|int $zaakId) {
        $zaak = $this->objectService->find($zaakId);

        $zaakArray = $zaak->jsonSerialize();

        // Delete connected objects
        $cascadeDeletes = array_merge(
            $zaakArray['rollen'],
            $zaakArray['eigenschappen'],
            $zaakArray['resultaat'],
            $zaakArray['status'], //?
            $zaakArray['deelzaken'],
            $zaakArray['zaakobjecten'],
            $zaakArray['zaakInformatieObjecten'],
            $zaakArray['klantcontact'],
        );

        $this->objectService->deleteObjects($cascadeDeletes);
    }

    /**
     * When a zaakinformatieobject is created in the ZRC, also create an objectinformatieobject in the DRC
     * ZRC-005
     *
     * @param string|int $zaakInformatieObjectId
     * @return void
     */
    public function createObjectInformatieObjectZaak(string|int $zaakInformatieObjectId): void
    {
        $oio = new ObjectEntity();
        $oio->setSchema($this->getOioSchema());
        $oio->setRegister($this->getDrcRegister());

        $zio = $this->objectService->find($zaakInformatieObjectId);
        $this->objectService->clearCurrents();

        $zioArray = $zio->jsonSerialize();

        $oioArray = [
            'object' => $zioArray['zaak'],
            'informatieoject' => $zioArray['informatieoject'],
        ];

        $oio->setObject($oioArray);

        $this->objectService->saveObject($oio, $oioArray);

    }

    /**
     * When a besluitinformatieobject is created in the BRC, also create an objectinformatieobject in the DRC
     * BRC-005
     *
     * @param string|int $besluitInformatieObjectId
     * @return void
     */
    public function createObjectInformatieObjectBesluit(string|int $besluitInformatieObjectId): void
    {
        $oio = new ObjectEntity();
        $oio->setSchema($this->getOioSchema());
        $oio->setRegister($this->getDrcRegister());

        $bio = $this->objectService->find($besluitInformatieObjectId);
        $this->objectService->clearCurrents();

        $bioArray = $bio->jsonSerialize();

        $oioArray = [
            'object' => $bioArray['besluit'],
            'informatieoject' => $bioArray['informatieoject'],
        ];

        $oio->setObject($oioArray);

        $this->objectService->saveObject($oio, $oioArray);

    }

    /**
     * Delete the objectInformatieObject if a ZaakInformatieobject or BesluitInformatieobject is deleted
     * Part of ZRC-023 and BRC-009
     *
     * @param string|int $objectId
     * @return void
     */
    public function deleteObjectInformatieObject(string|int $objectId): void
    {
        $object = $this->objectService->find($objectId);
        $serialized = $object->jsonSerialize();
        if($object->getSchema() === $this->getZioSchema()) {
            $objects = $this->objectService->findAll(['filters' => ['object' => $serialized['zaak'], 'objectType' => 'zaak', 'informatieobject' => $serialized['informatieobject'], 'register' => $this->getDrcRegister(), 'schema' => $this->getOioSchema()]]);

            $this->objectService->deleteObjects(array_map(function(ObjectEntity $object) {return $object->getUuid();}, $objects));

        }
        if($object->getSchema() === $this->getBioSchema()) {
            $objects = $this->objectService->findObjects(['filters' => ['object' => $serialized['besluit'], 'objectType' => 'besluit', 'informatieobject' => $serialized['informatieobject'], 'register' => $this->getDrcRegister(), 'schema' => $this->getOioSchema()]]);

            $this->objectService->deleteObjects(array_map(function(ObjectEntity $object) {return $object->getUuid();}, $objects));

        }
    }
}
