<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateInterval;
use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Db\DoesNotExistException;

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
            'gebruiksrechten' => 'gebruiksrechten',
            'zaakbesluit' => 'zaakbesluit',
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

    public function getGebruiksrechtenSchema(): string
    {
        return $this->schemas['gebruiksrechten'];
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

    public function getZaakBesluitSchema(): string
    {
        return $this->schemas['zaakbesluit'];
    }

    public function getObjectIdByEndpointUrl(string $url): string
    {
        $explodedUrl = explode('/', $url);
        return end($explodedUrl);
    }

    public function getObjectByEndpointUrl(string $url, array $extend = []): ObjectEntity
    {
        $this->objectService->clearCurrents();
        return $this->objectService->find(id: $this->getObjectIdByEndpointUrl($url), extend: $extend);

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
        $zaak = $this->objectService->find(id: end($explodedZaak), extend: ['zaakinformatieobjecten', 'zaakinformatieobjecten.informatieobject', /*'zaakinformatieobjecten.informatieobject.gebruiksrechten'*/]);
        $zaakArray = $zaak->jsonSerialize();

        // This only works if the relation between gebruiksrecht and informatieobject is properly set
        $gebruiksrechtenSet = array_map(function (array $zio) {
            $informatieobject = $zio['informatieobject'];
            return count($informatieobject['gebruiksrechten']) > 0 || $informatieobject['indicatieGebruiksrecht'] !== null;
        }, $zaakArray['zaakinformatieobjecten']);

        if (in_array(haystack: $gebruiksrechtenSet, needle: false) === true) {
                throw new CustomValidationException("Indicatiegebruiksrecht niet geset", [['name' => 'nonFieldErrors', 'code' => 'indicatiegebruiksrecht-unset', 'reason' => 'Alle informatieobjecten moeten een gebruiksrecht hebben voor een zaak kan worden gesloten.']]);
        }

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

        $zios = $zaakArray['zaakinformatieobjecten'];

        // Delete bio objects
        $zioIds = array_map(function(string $bio) {
            return $this->getObjectIdByEndpointUrl($bio);
        }, $zios);

        foreach($zioIds as $zioId) {
            $this->objectService->deleteObject($zioId);
        }
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
            $objects = $this->objectService->findAll(['filters' => ['object' => $serialized['besluit'], 'objectType' => 'besluit', 'informatieobject' => $serialized['informatieobject'], 'register' => $this->registerMapper->find($this->getDrcRegister())->getId(), 'schema' => $this->schemaMapper->find($this->getOioSchema())->getId()]]);

            $uuids = array_map(function(ObjectEntity $object) {return $object->getUuid();}, $objects);
            $this->objectService->deleteObjects($uuids);

        }
    }

    /**
     * ZRC-009: Set derived 'vertrouwelijkheidaanduiding' when 'vertrouwelijkheidaanduiding' is null
     *
     * @param ObjectEntity $zaak The 'zaak' for which vertrouwelijkheidaanduiding must be checked (and possibly derived).
     * @return void
     * @throws \Exception
     */
    public function setVertrouwelijkheidaanduiding(ObjectEntity $zaak): void
    {
        $zaakArray = $zaak->jsonSerialize();

        if ($zaakArray['vertrouwelijkheidaanduiding'] !== null) {
            return;
        }

        $zaaktypeId = explode('/', $zaakArray['zaaktype']);
        $zaaktypeId = end($zaaktypeId);
        $this->objectService->clearCurrents();
        $zaaktype = $this->objectService->find($zaaktypeId);

        $zaaktypeArray = $zaaktype->jsonSerialize();

        $zaakArray['vertrouwelijkheidaanduiding'] = $zaaktypeArray['vertrouwelijkheidaanduiding'];

        $zaak->setObject($zaakArray);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());


    }

    /**
     * ZRC-010: Validate if 'relevanteAndereZaken' contains valid references
     *
     * @param ObjectEntity $zaak The zaak resource to validate
     * @return void
     * @throws CustomValidationException
     */
    public function checkRelevanteAndereZaken (ObjectEntity $zaak): void
    {
        $zaakArray = $zaak->jsonSerialize();

        if (is_array($zaakArray['relevanteAndereZaken']) === false) {
            return;
        }

        $i = 0;
        foreach ($zaakArray['relevanteAndereZaken'] as $relevanteZaak) {
            $this->objectService->clearCurrents();
            if(isset($relevanteZaak['url']) === false) {
                $i++;
                continue;
            }
            try {
                $relevanteZaakId = explode('/', $relevanteZaak['url']);
                $relevanteZaakId = end($relevanteZaakId);
                $this->objectService->clearCurrents();
                $zaaktype = $this->objectService->find($relevanteZaakId);
                $this->objectService->clearCurrents();
            } catch (DoesNotExistException $exception) {
                throw new CustomValidationException("Relevante zaak bestaat niet", [['name' => "relevanteAndereZaken.$i.url", 'code' => 'bad-url', 'reason' => 'De relevante zaak bestaat niet of is niet benaderbaar']]);
            }
            $i++;
        }

    }

    /**
     * ZRC-015: Check if the values of 'productenOfDiensten' are also present in the 'productenOfDiensten' parameter of the 'zaaktype'
     *
     * @param ObjectEntity $zaak The zaak resource to validate
     * @return void
     * @throws CustomValidationException
     */
    public function checkProductenOfDiensten (ObjectEntity $zaak): void
    {
        $zaakArray = $zaak->jsonSerialize();

        if (is_array($zaakArray['productenOfDiensten']) === false) {
            return;
        }

        $zaaktypeId = explode('/', $zaakArray['zaaktype']);
        $zaaktypeId = end($zaaktypeId);
        $this->objectService->clearCurrents();
        $zaaktype = $this->objectService->find($zaaktypeId);
        $this->objectService->clearCurrents();

        $zaaktypeArray = $zaaktype->jsonSerialize();

        if(array_diff($zaakArray['productenOfDiensten'], $zaaktypeArray['productenOfDiensten']) !== []) {
            throw new CustomValidationException("Producten of diensten niet in lijn met zaaktype", [['name' => 'productenOfDiensten', 'code' => 'invalid-products-services', 'reason' => 'De producten en services zijn niet allemaal aanwezig op het zaaktype']]);
        }

    }

    /**
     * ZRC-022: Check if the archive-parameters are set properly before changing the archivation status
     *
     * @param ObjectEntity $zaak The zaak to validate
     * @return void
     */
    public function checkArchivePrerequisites (ObjectEntity $zaak): void
    {
        $zaakArray = $this->objectService->renderEntity($zaak);

        if($zaakArray['archiefstatus'] === 'nog_te_archiveren') {
            return;
        }

        $zioIds = array_map(function ($zio) {
            $exploded = explode('/', $zio);
            return end($exploded);
        }, $zaakArray['zaakinformatieobjecten']);

        $this->objectService->clearCurrents();
        $zios = $this->objectService->findAll(['ids' => $zioIds, 'extend' => ['informatieobject']]);

        $eioStatuses = array_unique(array_map(function (ObjectEntity $zio) {
            $zioArray = $zio->jsonSerialize();
            return $zioArray['informatieobject']['status'] ?? null;
        }, $zios));

        if(count($eioStatuses) !== 1 || $eioStatuses[0] !== 'gearchiveerd') {
            throw new CustomValidationException("Archivatieparameters zijn niet correct geset", [['name' => 'zaakinformatieobjecten', 'code' => 'informatieobject-status-not-set', 'reason' => 'De status van alle informatieobjecten moet \'gearchiveerd\' zijn voordat de zaak gearchiveerd kan worden.']]);
        }

        if ($zaakArray['archiefstatus'] !== 'nog_te_archiveren' && ($zaakArray['archiefnominatie'] === null)) {
            throw new CustomValidationException("Archivatieparameters zijn niet correct geset", [['name' => 'archiefnominatie', 'code' => 'archiefnominatie-not-set', 'reason' => 'De archiefnominatie moet geset zijn voordat de zaak gearchiveerd kan worden']]);

        }
        if ($zaakArray['archiefstatus'] !== 'nog_te_archiveren' && ($zaakArray['archiefactiedatum'] === null)) {
            throw new CustomValidationException("Archivatieparameters zijn niet correct geset", [['name' => 'archiefactiedatum', 'code' => 'archiefactiedatum-not-set', 'reason' => 'De archiefactiedatum moet geset zijn voordat de zaak gearchiveerd kan worden']]);

        }
    }

    /**
     * ZRC-012: Check the 'verlenging' and 'opschorting' parameters on a zaak resource
     *
     * @TODO: This should be done by the validator, but it does not validate subobjects at the moment.
     *
     * @param ObjectEntity $zaak
     * @return void
     * @throws CustomValidationException
     */
    public function checkGegevensgroepen(ObjectEntity $zaak): void
    {
        $zaakArray = $zaak->jsonSerialize();

        if ($zaakArray['verlenging'] === null && $zaakArray['opschorting'] === null) {
            return;
        }

        if ($zaakArray['verlenging'] !== null) {
            $unsetFields = array_diff(array_keys($zaakArray), ['reden', 'duur']);
            foreach($unsetFields as $field) {
                unset($zaakArray['verlenging'][$field]);
            }

            if(isset($zaakArray['verlenging']['reden']) === false) {
                $errors[] = ['name' => 'verlenging.reden', 'code' => 'required', 'reason' => 'Een verlenging moet het veld reden bevatten'];
            }
            if(isset($zaakArray['verlenging']['duur']) === false) {
                $errors[] = ['name' => 'verlenging.duur', 'code' => 'required', 'reason' => 'Een verlenging moet het veld duur bevatten'];
            }

            if(count($errors) !== 0) {
                throw new CustomValidationException(message: "Verlenging is incorrect", errors: $errors);
            }
        }

        if ($zaakArray['opschorting'] !== null) {
            $unsetFields = array_diff(array_keys($zaakArray), ['reden', 'indicatie']);
            foreach($unsetFields as $field) {
                unset($zaakArray['verlenging'][$field]);
            }

            $errors = [];

            if(isset($zaakArray['opschorting']['indicatie']) === false) {
                $errors[] = ['name' => 'opschorting.indicatie', 'code' => 'required', 'reason' => 'Een opschorting moet het veld indicatie bevatten'];
            }
            if(isset($zaakArray['opschorting']['reden']) === false) {
                $errors[] = ['name' => 'opschorting.reden', 'code' => 'required', 'reason' => 'Een opschorting moet het veld reden bevatten'];
            }

            if(count($errors) !== 0) {
                throw new CustomValidationException(message: "Opschorting is incorrect", errors: $errors);
            }
        }

    }

    public function createZaakBesluit(ObjectEntity $besluit): void
    {
        $besluitArray = $besluit->jsonSerialize();

        if (isset($besluitArray['zaak']) === false) {
            return;
        }

        $zaak = $this->getObjectByEndpointUrl(url: $besluitArray['zaak'], extend: ['zaaktype']);

        $zaakArray = $zaak->jsonSerialize();
        $zaaktypeArray = $zaakArray['zaaktype'];

        $besluittype = $this->getObjectByEndpointUrl($besluitArray['besluittype']);

        $besluittypeOmschrijving = $besluittype->jsonSerialize()['omschrijving'];

        if (in_array(needle: $besluittypeOmschrijving, haystack: $zaaktypeArray['besluittypen']) === false) {
            throw new CustomValidationException(message: 'Besluittype niet in zaaktype', errors: [['name' => 'nonFieldErrors', 'code' => 'invalid-besluittype', 'reason' => 'besluittype hoort niet bij het zaaktype van de zaak']]);
        }

        $zaakBesluit = new ObjectEntity();
        $zaakBesluit->setRegister($this->getZrcRegister());
        $zaakBesluit->setSchema($this->getZaakBesluitSchema());

        $zaakBesluit->setObject(
            [
                'zaak' => $besluitArray['zaak'],
                'besluit' => $besluitArray['url'], // TODO: Check if this is properly written.
            ]
        );

        $this->objectService->saveObject(object: $zaakBesluit, register: $zaakBesluit->getRegister(), schema: $zaakBesluit->getSchema());

    }

    public function validateBesluitInformatieObject(ObjectEntity $bio): void
    {
        $bioArray = $bio->jsonSerialize();

        $eio = $this->getObjectByEndpointUrl(url: $bioArray['informatieobject'], extend: ['informatieobjecttype']);
        $besluit = $this->getObjectByEndpointUrl(url: $bioArray['besluit'], extend: ['besluittype']);

        $eioIot = $eio->jsonSerialize()['informatieobjecttype']['omschrijving'];

        if (in_array(needle: $eioIot, haystack: $besluit->jsonSerialize()['besluittype']['informatieobjecttypen']) === false) {
            throw new CustomValidationException(message: 'Informatieobjecttype niet in besluittype', errors: [['name' => 'nonFieldErrors', 'code' => 'invalid-informatieobjecttype', 'reason' => 'informatieobjecttype niet aanwezig op besluittype']]);
        }
    }

    /**
     * Cascade deleting a zaak to delete BesluitInformatieObjecten
     *
     * @param ObjectEntity $besluit The besluit to be deleted.
     * @return void
     * @throws \Exception
     */
    public function deleteBesluit(ObjectEntity $besluit): void
    {
        $besluitArray = $this->objectService->renderEntity($besluit);


        // Delete bio objects
        $bioIds = array_map(function(string $bio) {
            return $this->getObjectIdByEndpointUrl($bio);
        }, $besluitArray['besluitinformatieobjecten']);

        foreach($bioIds as $bioId) {
            $this->objectService->deleteObject($bioId);
        }
    }
}
