<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Exception\CustomValidationException;
use RuntimeException;

/**
 * Service for ZGW OIO and besluit operations.
 *
 * Zaak lifecycle operations are in ZGWZaakLifecycleService.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZGWLogicService {

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
		$objectService = $mapperService->getOpenRegisters();
		if ($objectService === null) {
			throw new RuntimeException('ZGWLogicService requires the OpenRegister app to be installed and enabled.');
		}

		$this->objectService = $objectService;
	}//end __construct()

	/**
	 * Create an OIO for a zaakinformatieobject. ZRC-005.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function createObjectInformatieObjectZaak(ObjectEntity $zio): void {
		$arr = $zio->jsonSerialize();
		$this->createOio($arr['zaak'], $arr['informatieobject'], 'zaak');
	}//end createObjectInformatieObjectZaak()

	/**
	 * Create an OIO for a besluitinformatieobject. BRC-005.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function createObjectInformatieObjectBesluit(ObjectEntity $bio): void {
		$arr = $bio->jsonSerialize();
		$this->createOio($arr['besluit'], $arr['informatieobject'], 'besluit');
	}//end createObjectInformatieObjectBesluit()

	/**
	 * Delete OIO when a ZIO or BIO is deleted. ZRC-023 / BRC-009.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function deleteObjectInformatieObject(ObjectEntity $object, Schema $schema): void {
		$serialized = $object->jsonSerialize();

		if ($schema->getSlug() === $this->registry->getZioSchema()) {
			$this->deleteOioByFilters($serialized['zaak'], 'zaak', $serialized['informatieobject']);
		}

		if ($schema->getSlug() === $this->registry->getBioSchema()) {
			$this->deleteOioByFilters($serialized['besluit'], 'besluit', $serialized['informatieobject']);
		}
	}//end deleteObjectInformatieObject()

	/**
	 * Create a zaakbesluit when a besluit is created.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function createZaakBesluit(ObjectEntity $besluit): void {
		$arr = $besluit->jsonSerialize();

		if (isset($arr['zaak']) === false) {
			return;
		}

		$this->objectService->clearCurrents();
		$zaak = $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($arr['zaak']), _extend: ['zaaktype']);
		$this->objectService->clearCurrents();
		$besluittype = $this->objectService->find($this->registry->getObjectIdByEndpointUrl($arr['besluittype']));

		// besluittypen may be null when the zaaktype has no besluittypen configured (#282 bug-2).
		if (in_array(needle: $besluittype->jsonSerialize()['omschrijving'], haystack: $zaak->jsonSerialize()['zaaktype']['besluittypen'] ?? []) === false) {
			throw new CustomValidationException(
				'Besluittype niet in zaaktype',
				[['name' => 'nonFieldErrors', 'code' => 'invalid-besluittype', 'reason' => 'besluittype hoort niet bij het zaaktype van de zaak']]
			);
		}

		$zaakBesluit = new ObjectEntity();
		$zaakBesluit->setRegister($this->registry->getZrcRegister());
		$zaakBesluit->setSchema($this->registry->getZaakBesluitSchema());
		$zaakBesluit->setObject(['zaak' => $arr['zaak'], 'besluit' => $arr['url']]);
		$this->objectService->saveObject(object: $zaakBesluit, register: $zaakBesluit->getRegister(), schema: $zaakBesluit->getSchema());
	}//end createZaakBesluit()

	/**
	 * Cascade delete BesluitInformatieObjecten when a besluit is deleted.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function deleteBesluit(ObjectEntity $besluit): void {
		$arr = $this->objectService->renderEntity($besluit);
		foreach ($arr['besluitinformatieobjecten'] as $url) {
			$this->objectService->deleteObject($this->registry->getObjectIdByEndpointUrl($url));
		}
	}//end deleteBesluit()

	private function createOio(string $objectUrl, string $informatieobject, string $objectType): void {
		$oio = new ObjectEntity();
		$oio->setSchema($this->registry->getOioSchema());
		$oio->setRegister($this->registry->getDrcRegister());
		$oio->setObject(['object' => $objectUrl, 'informatieobject' => $informatieobject, 'objectType' => $objectType]);
		$this->objectService->saveObject(object: $oio, register: $this->registry->getDrcRegister(), schema: $this->registry->getOioSchema());
	}//end createOio()

	private function deleteOioByFilters(string $objectUrl, string $objectType, string $informatieobject): void {
		$objects = $this->objectService->findAll(
			[
				'filters' => [
					'object' => $objectUrl,
					'objectType' => $objectType,
					'informatieobject' => $informatieobject,
					'register' => $this->registerMapper->find($this->registry->getDrcRegister())->getId(),
					'schema' => $this->schemaMapper->find($this->registry->getOioSchema())->getId(),
				],
			]
		);

		$this->objectService->deleteObjects(array_map(fn (ObjectEntity $o) => $o->getUuid(), $objects));
	}//end deleteOioByFilters()

	private function getObjectByEndpointUrl(string $url, array $extend = []): ObjectEntity {
		$this->objectService->clearCurrents();
		return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), _extend: $extend);
	}//end getObjectByEndpointUrl()

	private function rewriteInternalReference(string $internalReference): string {
		return $this->getObjectByEndpointUrl($internalReference)->getUuid() ?? $internalReference;
	}//end rewriteInternalReference()

	/**
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function createZaakTypeInformatieObjecttype(ObjectEntity $ztIot):  void {
		$ztIotArray = $ztIot->jsonSerialize();

		$iotOmschrijving = $ztIotArray['informatieobjecttype'];

		$iots = $this->objectService->findAll(['filters' => ['omschrijving' => $iotOmschrijving, 'register' => $this->registerMapper->find($this->registry->getZtcRegister())->getId(), 'schema' => $this->schemaMapper->find($this->registry->getIOTSchema())->getId()]]);
		$this->objectService->clearCurrents();

		$zaaktype = $this->getObjectByEndpointUrl($ztIotArray['zaaktype']);
		$zaaktypeArray = $zaaktype->jsonSerialize();

		$iot = array_shift($iots);

		if ($iot === null) {
			throw new CustomValidationException(message: 'Informatieobjecttype en zaaktype behoren niet tot dezelfde catalogus', errors: [['name' => 'zaaktype', 'code' => 'catalogus', 'reason' => 'informatieobjecttype niet gevonden']]);
		}

		$iotArray = $iot->jsonSerialize();

		if ($zaaktypeArray['catalogus'] !== $iotArray['catalogus']) {
			throw new CustomValidationException(message: 'Informatieobjecttype en zaaktype behoren niet tot dezelfde catalogus', errors: [['name' => 'zaaktype', 'code' => 'catalogus', 'reason' => 'zaaktype niet in zelfde catalogus als informatieobjecttype']]);
		}

		$iotArray['zaaktypen'][] = $ztIotArray['zaaktype'];

		$iotArray['zaaktypen'] = array_unique($iotArray['zaaktypen']);

		$iot->setObject($iotArray);

		$this->objectService->saveObject(object: $iot, register: $this->registry->getZtcRegister(), schema: $this->registry->getIOTSchema());

		$zaaktypeArray['informatieobjecttypen'][] = $this->rewriteInternalReference($iotArray['url']);
		$zaaktypeArray['informatieobjecttypen'] = array_unique($zaaktypeArray['informatieobjecttypen']);
		$zaaktype->setObject($zaaktypeArray);

		$this->objectService->saveObject(object: $zaaktype, register: $this->registry->getZtcRegister(), schema: $this->registry->getZaakTypeSchema());

		$this->objectService->clearCurrents();

	}//end createZaakTypeInformatieObjecttype()

	/**
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function deleteZaakTypeInformatieObjecttype(ObjectEntity $ztIot):  void {
		$ztIotArray = $ztIot->jsonSerialize();

		$iotOmschrijving = $ztIotArray['informatieobjecttype'];

		$iots = $this->objectService->findAll(['filters' => ['omschrijving' => $iotOmschrijving, 'register' => $this->registerMapper->find($this->registry->getZtcRegister())->getId(), 'schema' => $this->schemaMapper->find($this->registry->getIOTSchema())->getId()]]);

		$iot = array_shift($iots);

		// Guard: array_shift returns null when no informatieobjecttype was found (#282 bug-3).
		if ($iot === null) {
			return;
		}

		$iotArray = $iot->jsonSerialize();

		$removeZaaktype = $ztIotArray['zaaktype'];

		$iotArray['zaaktypen'] = array_filter(
			$iotArray['zaaktypen'],
			function (string $zaaktype) use ($removeZaaktype) {
				return $zaaktype !== $removeZaaktype;
			}
		);

		$iot->setObject($iotArray);

		$this->objectService->saveObject(object: $iot, register: $this->registry->getZtcRegister(), schema: $this->registry->getIOTSchema());

		$zaaktype = $this->getObjectByEndpointUrl($removeZaaktype);
		$zaaktypeArray = $zaaktype->jsonSerialize();

		$removeIOT = $iotArray['id'];

		$zaaktypeArray['informatieobjecttypen'] = array_filter(
			$zaaktypeArray['informatieobjecttypen'],
			function (string $iotInZt) use ($removeIOT) {
				return $iotInZt !== $removeIOT;
			}
		);

		$zaaktype->setObject($zaaktypeArray);

		$this->objectService->saveObject(object: $zaaktype, register: $this->registry->getZtcRegister(), schema: $this->registry->getZaakTypeSchema());
		$this->objectService->clearCurrents();

	}//end deleteZaakTypeInformatieObjecttype()
}//end class
