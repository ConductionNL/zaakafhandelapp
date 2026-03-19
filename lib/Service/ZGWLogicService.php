<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Exception\CustomValidationException;

/**
 * Service for ZGW OIO and besluit operations.
 *
 * Zaak lifecycle operations are in ZGWZaakLifecycleService.
 */
class ZGWLogicService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    /**
     * @param ObjectMapperService $mapperService The mapper service
     * @param RegisterMapper $registerMapper The register mapper
     * @param SchemaMapper $schemaMapper The schema mapper
     * @param ZGWRegistryService $registry The registry service
     */
    public function __construct(
        ObjectMapperService $mapperService,
        private RegisterMapper $registerMapper,
        private SchemaMapper $schemaMapper,
        private ZGWRegistryService $registry,
    ) {
        $this->objectService = $mapperService->getOpenRegisters();
    }

    /**
     * Create an OIO for a zaakinformatieobject. ZRC-005.
     */
    public function createObjectInformatieObjectZaak(ObjectEntity $zio): void
    {
        $arr = $zio->jsonSerialize();
        $this->createOio($arr['zaak'], $arr['informatieobject'], 'zaak');
    }

    /**
     * Create an OIO for a besluitinformatieobject. BRC-005.
     */
    public function createObjectInformatieObjectBesluit(ObjectEntity $bio): void
    {
        $arr = $bio->jsonSerialize();
        $this->createOio($arr['besluit'], $arr['informatieobject'], 'besluit');
    }

    /**
     * Delete OIO when a ZIO or BIO is deleted. ZRC-023 / BRC-009.
     */
    public function deleteObjectInformatieObject(ObjectEntity $object, Schema $schema): void
    {
        $s = $object->jsonSerialize();

        if ($schema->getSlug() === $this->registry->getZioSchema()) {
            $this->deleteOioByFilters($s['zaak'], 'zaak', $s['informatieobject']);
        }

        if ($schema->getSlug() === $this->registry->getBioSchema()) {
            $this->deleteOioByFilters($s['besluit'], 'besluit', $s['informatieobject']);
        }
    }

    /**
     * Create a zaakbesluit when a besluit is created.
     */
    public function createZaakBesluit(ObjectEntity $besluit): void
    {
        $arr = $besluit->jsonSerialize();

        if (isset($arr['zaak']) === false) {
            return;
        }

        $this->objectService->clearCurrents();
        $zaak = $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($arr['zaak']), extend: ['zaaktype']);
        $this->objectService->clearCurrents();
        $bt = $this->objectService->find($this->registry->getObjectIdByEndpointUrl($arr['besluittype']));

        if (in_array(needle: $bt->jsonSerialize()['omschrijving'], haystack: $zaak->jsonSerialize()['zaaktype']['besluittypen']) === false) {
            throw new CustomValidationException(
                'Besluittype niet in zaaktype',
                [['name' => 'nonFieldErrors', 'code' => 'invalid-besluittype', 'reason' => 'besluittype hoort niet bij het zaaktype van de zaak']]
            );
        }

        $zb = new ObjectEntity();
        $zb->setRegister($this->registry->getZrcRegister());
        $zb->setSchema($this->registry->getZaakBesluitSchema());
        $zb->setObject(['zaak' => $arr['zaak'], 'besluit' => $arr['url']]);
        $this->objectService->saveObject(object: $zb, register: $zb->getRegister(), schema: $zb->getSchema());
    }

    /**
     * Cascade delete BesluitInformatieObjecten when a besluit is deleted.
     */
    public function deleteBesluit(ObjectEntity $besluit): void
    {
        $arr = $this->objectService->renderEntity($besluit);
        foreach ($arr['besluitinformatieobjecten'] as $url) {
            $this->objectService->deleteObject($this->registry->getObjectIdByEndpointUrl($url));
        }
    }

    private function createOio(string $objectUrl, string $informatieobject, string $objectType): void
    {
        $oio = new ObjectEntity();
        $oio->setSchema($this->registry->getOioSchema());
        $oio->setRegister($this->registry->getDrcRegister());
        $oio->setObject(['object' => $objectUrl, 'informatieobject' => $informatieobject, 'objectType' => $objectType]);
        $this->objectService->saveObject(object: $oio, register: $this->registry->getDrcRegister(), schema: $this->registry->getOioSchema());
    }

    private function deleteOioByFilters(string $objectUrl, string $objectType, string $informatieobject): void
    {
        $objects = $this->objectService->findAll([
            'filters' => [
                'object' => $objectUrl,
                'objectType' => $objectType,
                'informatieobject' => $informatieobject,
                'register' => $this->registerMapper->find($this->registry->getDrcRegister())->getId(),
                'schema' => $this->schemaMapper->find($this->registry->getOioSchema())->getId(),
            ],
        ]);

        $this->objectService->deleteObjects(array_map(fn(ObjectEntity $o) => $o->getUuid(), $objects));
    }

    private function getObjectByEndpointUrl(string $url, array $extend = []): ObjectEntity
    {
        $this->objectService->clearCurrents();
        return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), extend: $extend);
    }

    private function rewriteInternalReference(string $internalReference): string
    {
        return $this->getObjectByEndpointUrl($internalReference);
    }

    public function createZaakTypeInformatieObjecttype (ObjectEntity $ztIot):  void
    {
        $ztIotArray = $ztIot->jsonSerialize();

        $informatieObjectTypeOmschrijving = $ztIotArray['informatieobjecttype'];

        $iots = $this->objectService->findAll(['filters' => ['omschrijving' => $informatieObjectTypeOmschrijving, 'register' => $this->registerMapper->find($this->registry->getZtcRegister())->getId(), 'schema'=> $this->schemaMapper->find($this->registry->getIOTSchema())->getId()]]);
        $this->objectService->clearCurrents();

        $zt = $this->getObjectByEndpointUrl($ztIotArray['zaaktype']);
        $ztArray = $zt->jsonSerialize();

        /** @var ObjectEntity $iot */
        $iot = array_shift($iots);

        if($iot === null) {
            throw new CustomValidationException(message: 'Informatieobjecttype en zaaktype behoren niet tot dezelfde catalogus', errors: [['name' => 'zaaktype', 'code' => 'catalogus', 'reason' => 'informatieobjecttype niet gevonden']]);
        }

        $iotArray = $iot->jsonSerialize();

        if($ztArray['catalogus'] !== $iotArray['catalogus']) {
            throw new CustomValidationException(message: 'Informatieobjecttype en zaaktype behoren niet tot dezelfde catalogus', errors: [['name' => 'zaaktype', 'code' => 'catalogus', 'reason' => 'zaaktype niet in zelfde catalogus als informatieobjecttype']]);
        }

        $iotArray['zaaktypen'][] = $ztIotArray['zaaktype'];

        $iotArray['zaaktypen'] = array_unique($iotArray['zaaktypen']);

        $iot->setObject($iotArray);

        $this->objectService->saveObject(object: $iot, register: $this->registry->getZtcRegister(), schema: $this->registry->getIOTSchema());

        $ztArray['informatieobjecttypen'][] = $this->rewriteInternalReference($iotArray['url']);
        $ztArray['informatieobjecttypen'] = array_unique($ztArray['informatieobjecttypen']);
        $zt->setObject($ztArray);

        $this->objectService->saveObject(object: $zt, register: $this->registry->getZtcRegister(), schema: $this->registry->getZaakTypeSchema());


        $this->objectService->clearCurrents();

    }
    public function deleteZaakTypeInformatieObjecttype (ObjectEntity $ztIot):  void
    {
        $ztIotArray = $ztIot->jsonSerialize();

        $informatieObjectTypeOmschrijving = $ztIotArray['informatieobjecttype'];

        $iots = $this->objectService->findAll(['filters' => ['omschrijving' => $informatieObjectTypeOmschrijving, 'register' => $this->registerMapper->find($this->registry->getZtcRegister())->getId(), 'schema'=> $this->schemaMapper->find($this->registry->getIOTSchema())->getId()]]);

        /** @var ObjectEntity $iot */
        $iot = array_shift($iots);
        $iotArray = $iot->jsonSerialize();

        $removeZaaktype = $ztIotArray['zaaktype'];

        $iotArray['zaaktypen'] = array_filter($iotArray['zaaktypen'], function(string $zaaktype) use ($removeZaaktype) {
            return $zaaktype !== $removeZaaktype;
        });

        $iot->setObject($iotArray);

        $this->objectService->saveObject(object: $iot, register: $this->registry->getZtcRegister(), schema: $this->registry->getIOTSchema());

        $zt = $this->getObjectByEndpointUrl($removeZaaktype);

        $ztArray = $zt->jsonSerialize();

        $removeIOT = $iotArray['id'];

        $ztArray['informatieobjecttypen'] = array_filter($ztArray['informatieobjecttypen'], function (string $iotInZt) use ($removeIOT) {
            return $iotInZt !== $removeIOT;
        });

        $zt->setObject($ztArray);

        $this->objectService->saveObject(object: $zt, register: $this->registry->getZtcRegister(), schema: $this->registry->getZaakTypeSchema());
        $this->objectService->clearCurrents();

    }
}
