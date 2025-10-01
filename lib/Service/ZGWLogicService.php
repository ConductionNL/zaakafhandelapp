<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateInterval;
use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
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
        ObjectService $objectService,
        private RegisterMapper $registerMapper,
        private SchemaMapper $schemaMapper,
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
            'zaak'    => 'zaak',
            'status'  => 'status',
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

    public function getZaakSchema(): string
    {
        return $this->schemas['zaak'];
    }

    public function getStatusSchema(): string
    {
        return $this->schemas['status'];
    }

    /**
     * When eindstatus is set on a zaak, close the zaak by setting appropriate values
     * ZRC-007 and ZRC-021
     *
     * @param string|int $statusId
     * @return void
     */
    public function closeZaak(ObjectEntity $status): void
    {
        $statusArray = $status->jsonSerialize();

        // Validate endStatus
        $explodedStatusType = explode('/', $statusArray['statustype']);
        $this->objectService->clearCurrents();
        $statusType = $this->objectService->find(id: end($explodedStatusType), extend: ['_extend.zaaktype' => 'zaaktype', '_extend.statustypen' =>'zaaktype.statustypen']);

        $maxOrder = max(array_map(function(array $st) {
            return $st['volgnummer'];
        }, $statusType->jsonSerialize()['_extend']['zaaktype']['_extend']['statustypen']));

        if($statusType->jsonSerialize()['volgnummer'] !== $maxOrder) {
            return;
        }

        // Get zaak
        $explodedZaak = explode('/', $statusArray['zaak']);
        $this->objectService->clearCurrents();
        $zaak = $this->objectService->find(end($explodedZaak));

        $zaakArray = $zaak->jsonSerialize();

        //Set fields for closed zaak
        $zaakArray['einddatum'] = (new DateTime($statusArray['datumStatusGezet']))->format("Y-m-d");

        $explodedResultaat = explode('/', $zaakArray['resultaat']);
        $this->objectService->clearCurrents();
        $resultaat = $this->objectService->find(end($explodedResultaat));

        $explodedResultaattype = explode('/', $resultaat->jsonSerialize()['resultaattype']);
        $this->objectService->clearCurrents();
        $resultaatType = $this->objectService->find(end($explodedResultaattype));

        $resultaattypeArray = $resultaatType->jsonSerialize();

        $zaakArray['archiefnominatie'] = $resultaattypeArray['archiefnominatie'];

        $afleidingswijze = $resultaattypeArray['brondatumArchiefprocedure']['afleidingswijze'] ?? null;
        switch($afleidingswijze) {
            case 'afgehandeld':
                $zaakArray['archiefactiedatum'] = $zaakArray['einddatum'];
                break;
            case 'hoofdzaak':
                $hoofdzaakId = explode('/', $zaakArray['hoofdzaak']);
                $hoofdzaakId = end($hoofdzaakId);
                $this->objectService->clearCurrents();
                $hoofdzaak = $this->objectService->find($hoofdzaakId);

                $zaak['archiefactiedatum'] = $hoofdzaak->jsonSerialize()['einddatum'];
                break;
            case 'eigenschap':
                $eigenschap = $resultaattypeArray['brondatumArchiefprocedure']['datumkenmerk'] ?? null;
                $eigenschapIds = array_map(function ($eigenschap) {
                    $exploded = explode('/', $eigenschap);
                    return end($exploded);
                }, $zaakArray['eigenschappen']);
                $this->objectService->clearCurrents();
                $eigenschappen = $this->objectService->findAll(['ids' => $eigenschapIds]);
                $eigenschapObjects = array_filter($eigenschappen, function (ObjectEntity $eigenschapObject) use ($eigenschap) {return $eigenschapObject->jsonSerialize()['naam'] === $eigenschap;});
                $eigenschapObject = array_shift($eigenschapObjects);

                $zaakArray['archiefactiedatum'] = $eigenschapObject->jsonSerialize()['waarde'];
                break;
            case 'ander_datumkenmerk':
                $zaakArray['archiefactiedatum'] = null;
                break;
            case 'termijn':
                $zaakArray['archiefactiedatum'] = (new DateTime($zaakArray['einddatum']))->add(new DateInterval($resultaattypeArray['brondatumArchiefprocedure']['procestermijn']));
                break;
            case 'ingangsdatum_besluit':
            case 'vervaldatum_besluit':
                // Can we please stop breaking performance?
                // fetch besluiten
                $this->objectService->clearCurrents();
                $besluiten = $this->objectService->findAll(['filters' => ['zaak' => $zaakArray['url'], 'register' => $this->getBrcRegister(), 'schema'=> $this->getBesluitSchema()]]);

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

        $this->objectService->clearCurrents();

        // Save zaak
        $zaak->setObject($zaakArray);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }

    /**
     * When a status is set that is not an eindstatus, unset appropriate values for closed zaak
     * ZRC-008
     *
     * @param string|int $statusId
     * @return void
     */
    public function reopenZaak(ObjectEntity $status): void
    {

        $statusArray = $status->jsonSerialize();

        // Validate not endStatus
        $explodedStatusType = explode('/', $statusArray['statustype']);
        $this->objectService->clearCurrents();
        $statusType = $this->objectService->find(id: end($explodedStatusType), extend: ['_extend.zaaktype' => 'zaaktype', '_extend.statustypen' =>'zaaktype.statustypen']);

        $maxOrder = max(array_map(function(array $st) {
            return $st['volgnummer'];
        }, $statusType->jsonSerialize()['_extend']['zaaktype']['_extend']['statustypen']));

        if($statusType->jsonSerialize()['volgnummer'] === $maxOrder) {
            return;
        }

        // Get zaak
        $explodedZaak = explode('/', $statusArray['zaak']);
        $this->objectService->clearCurrents();
        $zaak = $this->objectService->find(end($explodedZaak));

        $zaakArray = $zaak->jsonSerialize();

        // Unset fields for closed zaak
        $zaakArray['einddatum'] = null;
        $zaakArray['archiefactiedatum'] = null;
        $zaakArray['archiefnominatie'] = null;

        // Save zaak
        $zaak->setObject($zaakArray);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }

    /**
     * Delete objects that are dependent on a zaak when the zaak is deleted
     * ZRC-023
     *
     * @param string|int $zaakId
     * @return void
     */
    public function deleteZaak(ObjectEntity $zaak): void
    {

        $zaakArray = $this->objectService->renderEntity($zaak);


        // Delete connected objects
        $cascadeDeletes = array_merge(
            $zaakArray['rollen'] ?? [],
            $zaakArray['eigenschappen'] ?? [],
            [$zaakArray['resultaat']],
            $zaakArray['statussen'] ?? [],
            $zaakArray['deelzaken'] ?? [],
            $zaakArray['zaakobjecten'] ?? [],
            $zaakArray['zaakinformatieobjecten'] ?? [],
            [$zaakArray['klantcontact']],
        );

        $cascadeDeletes = array_map(function (?string $url) {
            if($url === null) {
                return null;
            }

            $explodedUrl = explode('/', $url);
            return end($explodedUrl);
        }, $cascadeDeletes);

        $cascadeDeletes = array_filter($cascadeDeletes);


        $this->objectService->deleteObjects($cascadeDeletes);
    }

    /**
     * When a zaakinformatieobject is created in the ZRC, also create an objectinformatieobject in the DRC
     * ZRC-005
     *
     * @param string|int $zaakInformatieObjectId
     * @return void
     */
    public function createObjectInformatieObjectZaak(ObjectEntity $zio): void
    {
        $oio = new ObjectEntity();
        $oio->setSchema($this->getOioSchema());
        $oio->setRegister($this->getDrcRegister());

        $zioArray = $zio->jsonSerialize();

        $oioArray = [
            'object' => $zioArray['zaak'],
            'informatieobject' => $zioArray['informatieobject'],
            'objectType' => 'zaak'
        ];

        $oio->setObject($oioArray);

        $this->objectService->saveObject(object: $oio, register: $this->getDrcRegister(), schema: $this->getOioSchema());

    }

    /**
     * When a besluitinformatieobject is created in the BRC, also create an objectinformatieobject in the DRC
     * BRC-005
     *
     * @param string|int $besluitInformatieObjectId
     * @return void
     */
    public function createObjectInformatieObjectBesluit(ObjectEntity $bio): void
    {
        $oio = new ObjectEntity();
        $oio->setSchema($this->getOioSchema());
        $oio->setRegister($this->getDrcRegister());

        $bioArray = $bio->jsonSerialize();

        $oioArray = [
            'object' => $bioArray['besluit'],
            'informatieobject' => $bioArray['informatieobject'],
            'objectType' => 'besluit'
        ];

        $oio->setObject($oioArray);

        $this->objectService->saveObject(object: $oio, register: $this->getDrcRegister(), schema: $this->getOioSchema());

    }

    /**
     * Delete the objectInformatieObject if a ZaakInformatieobject or BesluitInformatieobject is deleted
     * Part of ZRC-023 and BRC-009
     *
     * @param string|int $objectId
     * @return void
     */
    public function deleteObjectInformatieObject(ObjectEntity $object, Schema $schema): void
    {
        $serialized = $object->jsonSerialize();

        if($schema->getSlug() === $this->getZioSchema()) {
            $objects = $this->objectService->findAll(['filters' => ['object' => $serialized['zaak'], 'objectType' => 'zaak', 'informatieobject' => $serialized['informatieobject'], 'register' => $this->registerMapper->find($this->getDrcRegister())->getId(), 'schema' => $this->schemaMapper->find($this->getOioSchema())->getId()]]);

            $this->objectService->deleteObjects(array_map(function(ObjectEntity $object) {return $object->getUuid();}, $objects));

        }
        if($schema->getSlug() === $this->getBioSchema()) {
            $objects = $this->objectService->findObjects(['filters' => ['object' => $serialized['besluit'], 'objectType' => 'besluit', 'informatieobject' => $serialized['informatieobject'], 'register' => $this->registerMapper->find($this->getDrcRegister())->getId(), 'schema' => $this->schemaMapper->find($this->getOioSchema())->getId()]]);

            $this->objectService->deleteObjects(array_map(function(ObjectEntity $object) {return $object->getUuid();}, $objects));

        }
    }
}
