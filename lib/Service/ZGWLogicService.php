<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Exception\CustomValidationException;
use Psr\Log\LoggerInterface;
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
	 * @param LoggerInterface $logger The logger
	 */
	public function __construct(
		ObjectMapperService $mapperService,
		private RegisterMapper $registerMapper,
		private SchemaMapper $schemaMapper,
		private ZGWRegistryService $registry,
		private LoggerInterface $logger,
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
		$this->createOio(
			$this->zioCaseUrl($arr),
			$this->requiredLink($zio, $arr, 'informatieobject'),
			'zaak'
		);
	}//end createObjectInformatieObjectZaak()


	/**
	 * The case URL a zaakinformatieobject points at, whichever key it was stored under.
	 *
	 * A ZIO reaches us from two producers that disagree about the property name.
	 * This app's own ZaakInformatieObjectenController requires `zaak`. Dossiq's
	 * ZgwZrcZaakinformatieobjectRules stores `case`, and has since it renamed the
	 * property. Reading only `zaak` therefore handed null to createOio(), whose
	 * first parameter is typed string, and the request died as
	 * `createOio(): Argument #1 ($objectUrl) must be of type string, null given`
	 * with no mention of the property that was actually missing.
	 *
	 * It reproduces only where both apps are installed, so CI does not see it.
	 *
	 * @param array<string,mixed> $arr The serialized zaakinformatieobject.
	 *
	 * @return string The case URL.
	 *
	 * @throws RuntimeException When the ZIO carries neither key, which is a broken
	 *                          record rather than a producer disagreement, and is
	 *                          worth saying so by name.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	private function zioCaseUrl(array $arr): string {
		$url = ($arr['case'] ?? $arr['zaak'] ?? null);

		if (is_string($url) === false || $url === '') {
			throw new RuntimeException(
				'A zaakinformatieobject must carry the case it belongs to, as either `case` '
				. '(dossiq) or `zaak` (this app); this one carries neither.'
			);
		}

		return $url;
	}//end zioCaseUrl()

	/**
	 * Create an OIO for a besluitinformatieobject. BRC-005.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function createObjectInformatieObjectBesluit(ObjectEntity $bio): void {
		$arr = $bio->jsonSerialize();
		$this->createOio(
			$this->requiredLink($bio, $arr, 'besluit'),
			$this->requiredLink($bio, $arr, 'informatieobject'),
			'besluit'
		);
	}//end createObjectInformatieObjectBesluit()

	/**
	 * Delete OIO when a ZIO or BIO is deleted. ZRC-023 / BRC-009.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function deleteObjectInformatieObject(ObjectEntity $object, Schema $schema): void {
		$serialized = $object->jsonSerialize();

		if ($schema->getSlug() === $this->registry->getZioSchema()) {
			// Same two producers, same disagreement: see zioCaseUrl(). Deleting by
			// the wrong key would leave the OIO behind rather than fail loudly.
			$this->deleteOioByFilters(
				$this->zioCaseUrl($serialized),
				'zaak',
				$this->requiredLink($object, $serialized, 'informatieobject')
			);
		}

		if ($schema->getSlug() === $this->registry->getBioSchema()) {
			$this->deleteOioByFilters(
				$this->requiredLink($object, $serialized, 'besluit'),
				'besluit',
				$this->requiredLink($object, $serialized, 'informatieobject')
			);
		}
	}//end deleteObjectInformatieObject()

	/**
	 * Create a zaakbesluit when a besluit is created.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function createZaakBesluit(ObjectEntity $decision): void {
		$arr = $decision->jsonSerialize();

		if (isset($arr['zaak']) === false) {
			return;
		}

		$this->objectService->clearCurrents();
		$case = $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($arr['zaak']), _extend: ['zaaktype']);
		$this->objectService->clearCurrents();
		$besluittype = $this->objectService->find($this->registry->getObjectIdByEndpointUrl($arr['besluittype']));

		// besluittypen may be null when the zaaktype has no besluittypen configured (#282 bug-2).
		if (in_array(needle: $besluittype->jsonSerialize()['omschrijving'], haystack: $case->jsonSerialize()['zaaktype']['besluittypen'] ?? []) === false) {
			throw new CustomValidationException(
				'Besluittype niet in zaaktype',
				[['name' => 'nonFieldErrors', 'code' => 'invalid-besluittype', 'reason' => 'besluittype hoort niet bij het zaaktype van de zaak']]
			);
		}

		$caseDecision = new ObjectEntity();
		$caseDecision->setRegister($this->registry->getZrcRegister());
		$caseDecision->setSchema($this->registry->getZaakBesluitSchema());
		$caseDecision->setObject(['zaak' => $arr['zaak'], 'besluit' => $arr['url']]);
		$this->objectService->saveObject(object: $caseDecision, register: $caseDecision->getRegister(), schema: $caseDecision->getSchema());
	}//end createZaakBesluit()

	/**
	 * Cascade delete BesluitInformatieObjecten when a besluit is deleted.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function deleteBesluit(ObjectEntity $decision): void {
		$arr = $this->objectService->renderEntity($decision);
		foreach ($arr['besluitinformatieobjecten'] as $url) {
			$this->objectService->deleteObject($this->registry->getObjectIdByEndpointUrl($url));
		}
	}//end deleteBesluit()

	/**
	 * Read a required relation off a ZGW object, refusing the cascade when it is absent.
	 *
	 * Reached only for an object this app owns — ZGWZaakEventHandler has already
	 * established that. So a missing link here is malformed ZGW data, not another
	 * app's schema that happens to share a slug, and it must not pass quietly: the
	 * cascade this method feeds is the whole reason the write triggers ZGW logic.
	 *
	 * Before this guard the value went straight into a `string` parameter and the
	 * resulting TypeError surfaced as an unexplained HTTP 500 on the object write.
	 *
	 * @param ObjectEntity $object The object carrying the relation.
	 * @param array<string,mixed> $data Its serialized payload.
	 * @param string $field The relation field that must be present.
	 *
	 * @return string The relation URL.
	 *
	 * @throws CustomValidationException When the relation is missing or not a URL string.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	private function requiredLink(ObjectEntity $object, array $data, string $field): string {
		$value = $this->optionalLink($object, $data, $field);
		if ($value !== null) {
			return $value;
		}

		throw new CustomValidationException(
			message: sprintf('%s ontbreekt', $field),
			errors: [
				[
					'name' => $field,
					'code' => 'required',
					'reason' => sprintf('%s is verplicht om de objectinformatieobject-relatie te leggen', $field),
				],
			]
		);
	}//end requiredLink()

	/**
	 * The same read, for a caller that has to continue without the relation.
	 *
	 * Returns null rather than throwing, but never silently: a missing relation on
	 * an object this app owns is logged at error level with the identifiers needed
	 * to find the row.
	 *
	 * @param ObjectEntity $object The object carrying the relation.
	 * @param array<string,mixed> $data Its serialized payload.
	 * @param string $field The relation field to read.
	 *
	 * @return string|null The relation URL, or null when it is absent.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	private function optionalLink(ObjectEntity $object, array $data, string $field): ?string {
		$value = ($data[$field] ?? null);
		if (is_string($value) === true && trim($value) !== '') {
			return $value;
		}

		$this->logger->error(
			'ZaakAfhandelApp: ZGW relation object is missing a required link, cascade skipped',
			[
				'field' => $field,
				'object' => $object->getUuid(),
				'register' => $object->getRegister(),
				'schema' => $object->getSchema(),
				'presentFields' => array_keys($data),
			]
		);

		return null;
	}//end optionalLink()

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

		$caseType = $this->getObjectByEndpointUrl($ztIotArray['zaaktype']);
		$caseTypeArray = $caseType->jsonSerialize();

		$iot = array_shift($iots);

		if ($iot === null) {
			throw new CustomValidationException(message: 'Informatieobjecttype en zaaktype behoren niet tot dezelfde catalogus', errors: [['name' => 'zaaktype', 'code' => 'catalogus', 'reason' => 'informatieobjecttype niet gevonden']]);
		}

		$iotArray = $iot->jsonSerialize();

		if ($caseTypeArray['catalogus'] !== $iotArray['catalogus']) {
			throw new CustomValidationException(message: 'Informatieobjecttype en zaaktype behoren niet tot dezelfde catalogus', errors: [['name' => 'zaaktype', 'code' => 'catalogus', 'reason' => 'zaaktype niet in zelfde catalogus als informatieobjecttype']]);
		}

		$iotArray['zaaktypen'][] = $ztIotArray['zaaktype'];

		$iotArray['zaaktypen'] = array_unique($iotArray['zaaktypen']);

		$iot->setObject($iotArray);

		$this->objectService->saveObject(object: $iot, register: $this->registry->getZtcRegister(), schema: $this->registry->getIOTSchema());

		$caseTypeArray['informatieobjecttypen'][] = $this->rewriteInternalReference($iotArray['url']);
		$caseTypeArray['informatieobjecttypen'] = array_unique($caseTypeArray['informatieobjecttypen']);
		$caseType->setObject($caseTypeArray);

		$this->objectService->saveObject(object: $caseType, register: $this->registry->getZtcRegister(), schema: $this->registry->getZaakTypeSchema());

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

		$removeCaseType = $ztIotArray['zaaktype'];

		$iotArray['zaaktypen'] = array_filter(
			$iotArray['zaaktypen'],
			function (string $caseType) use ($removeCaseType) {
				return $caseType !== $removeCaseType;
			}
		);

		$iot->setObject($iotArray);

		$this->objectService->saveObject(object: $iot, register: $this->registry->getZtcRegister(), schema: $this->registry->getIOTSchema());

		$caseType = $this->getObjectByEndpointUrl($removeCaseType);
		$caseTypeArray = $caseType->jsonSerialize();

		$removeIOT = $iotArray['id'];

		$caseTypeArray['informatieobjecttypen'] = array_filter(
			$caseTypeArray['informatieobjecttypen'],
			function (string $iotInZt) use ($removeIOT) {
				return $iotInZt !== $removeIOT;
			}
		);

		$caseType->setObject($caseTypeArray);

		$this->objectService->saveObject(object: $caseType, register: $this->registry->getZtcRegister(), schema: $this->registry->getZaakTypeSchema());
		$this->objectService->clearCurrents();

	}//end deleteZaakTypeInformatieObjecttype()
}//end class
