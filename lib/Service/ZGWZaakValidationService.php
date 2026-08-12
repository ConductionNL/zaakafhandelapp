<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;
use RuntimeException;

/**
 * Validation service for zaak-specific field validation.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZGWZaakValidationService {

	/**
	 * The OpenRegister object service used to resolve related ZGW objects.
	 *
	 * @var \OCA\OpenRegister\Service\ObjectService
	 */
	private \OCA\OpenRegister\Service\ObjectService $objectService;

	/**
	 * Constructor.
	 *
	 * @param ObjectMapperService $mapperService The OpenRegister mapper service.
	 *
	 * @throws RuntimeException When the OpenRegister app is not available.
	 */
	public function __construct(ObjectMapperService $mapperService) {
		$objectService = $mapperService->getOpenRegisters();
		if ($objectService === null) {
			throw new RuntimeException('ZGWZaakValidationService requires the OpenRegister app to be installed and enabled.');
		}

		$this->objectService = $objectService;
	}//end __construct()

	/**
	 * ZRC-015: Check productenOfDiensten against zaaktype.
	 *
	 * @param ObjectEntity $zaak The zaak being validated.
	 *
	 * @return void
	 *
	 * @throws CustomValidationException When the zaak references a product the zaaktype does not allow.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-005
	 */
	public function checkProductenOfDiensten(ObjectEntity $zaak): void {
		$arr = $zaak->jsonSerialize();

		if (is_array($arr['productenOfDiensten'] ?? null) === false) {
			// No producten/diensten configured on the zaak; nothing to validate.
			return;
		}

		$zaaktypeUrl = $arr['zaaktype'] ?? null;
		if ($zaaktypeUrl === null || $zaaktypeUrl === '') {
			// Without a zaaktype there is no reference set to validate against.
			return;
		}

		$ztId = explode('/', $zaaktypeUrl);
		$this->objectService->clearCurrents();
		$zaaktype = $this->objectService->find(end($ztId));

		$allowed = $zaaktype->jsonSerialize()['productenOfDiensten'] ?? [];
		if (is_array($allowed) === false) {
			$allowed = [];
		}

		if (array_diff($arr['productenOfDiensten'], $allowed) !== []) {
			$this->throwValidationError(
				name: 'productenOfDiensten',
				code: 'invalid-products-services',
				reason: 'Producten niet aanwezig op zaaktype'
			);
		}
	}//end checkProductenOfDiensten()

	/**
	 * Throw a single-field ZGW validation error.
	 *
	 * @param string $name The name of the offending field.
	 * @param string $code The machine-readable error code.
	 * @param string $reason The human-readable reason, also used as the exception message.
	 *
	 * @return void
	 *
	 * @throws CustomValidationException Always.
	 */
	private function throwValidationError(string $name, string $code, string $reason): void {
		throw new CustomValidationException($reason, [['name' => $name, 'code' => $code, 'reason' => $reason]]);
	}//end throwValidationError()

	/**
	 * ZRC-022: Check archive prerequisites.
	 *
	 * @param ObjectEntity $zaak The zaak being validated.
	 *
	 * @return void
	 *
	 * @throws CustomValidationException When an archive prerequisite is not met.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-005
	 */
	public function checkArchivePrerequisites(ObjectEntity $zaak): void {
		$arr = $this->objectService->renderEntity($zaak);

		// A zaak that is not (yet) flagged for archiving has no archive prerequisites.
		// When the archive lifecycle has not started, archiefstatus is either absent
		// (e.g. on a fresh create where the form does not expose the field) or the
		// explicit 'nog_te_archiveren' value — in both cases there is nothing to enforce.
		$archiefstatus = $arr['archiefstatus'] ?? null;
		if ($archiefstatus === null || $archiefstatus === '' || $archiefstatus === 'nog_te_archiveren') {
			return;
		}

		$this->validateEioStatuses(arr: $arr);

		if (($arr['archiefnominatie'] ?? null) === null) {
			$this->throwValidationError(
				name: 'archiefnominatie',
				code: 'archiefnominatie-not-set',
				reason: 'De archiefnominatie moet geset zijn'
			);
		}

		if (($arr['archiefactiedatum'] ?? null) === null) {
			$this->throwValidationError(
				name: 'archiefactiedatum',
				code: 'archiefactiedatum-not-set',
				reason: 'De archiefactiedatum moet geset zijn'
			);
		}
	}//end checkArchivePrerequisites()

	/**
	 * ZRC-012: Check verlenging and opschorting parameters.
	 *
	 * @param ObjectEntity $zaak The zaak being validated.
	 *
	 * @return void
	 *
	 * @throws CustomValidationException When a gegevensgroep misses a required field.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-005
	 */
	public function checkGegevensgroepen(ObjectEntity $zaak): void {
		$arr = $zaak->jsonSerialize();

		if (($arr['verlenging'] ?? null) !== null) {
			$this->validateRequiredFields(
				data: $arr['verlenging'],
				group: 'verlenging',
				fields: ['reden', 'duur'],
				message: 'Verlenging is incorrect'
			);
		}

		if (($arr['opschorting'] ?? null) !== null) {
			$this->validateRequiredFields(
				data: $arr['opschorting'],
				group: 'opschorting',
				fields: ['indicatie', 'reden'],
				message: 'Opschorting is incorrect'
			);
		}
	}//end checkGegevensgroepen()

	/**
	 * Assert every informatieobject linked to the zaak has status "gearchiveerd".
	 *
	 * @param array $arr The rendered zaak, including its zaakinformatieobjecten.
	 *
	 * @return void
	 *
	 * @throws CustomValidationException When any informatieobject is not archived.
	 */
	private function validateEioStatuses(array $arr): void {
		// Guard: a brand-new zaak may have no informatieobjecten yet; skip the check in that case.
		if (empty($arr['zaakinformatieobjecten']) === true) {
			return;
		}

		$zioIds = array_map(
			function ($zio) {
				$e = explode('/', $zio);
				return end($e);
			},
			$arr['zaakinformatieobjecten']
		);

		$this->objectService->clearCurrents();
		$zios = $this->objectService->findAll(['ids' => $zioIds, 'extend' => ['informatieobject']]);
		$statuses = array_unique(array_map(fn (ObjectEntity $zio) => $zio->jsonSerialize()['informatieobject']['status'] ?? null, $zios));

		if (count($statuses) !== 1 || $statuses[0] !== 'gearchiveerd') {
			$this->throwValidationError(
				name: 'zaakinformatieobjecten',
				code: 'informatieobject-status-not-set',
				reason: 'Alle informatieobjecten moeten status gearchiveerd hebben.'
			);
		}
	}//end validateEioStatuses()

	/**
	 * Assert a gegevensgroep contains every required field.
	 *
	 * @param array $data The gegevensgroep payload to inspect.
	 * @param string $group The gegevensgroep name, used to prefix the error field names.
	 * @param array $fields The names of the fields that must be present.
	 * @param string $message The exception message used when a field is missing.
	 *
	 * @return void
	 *
	 * @throws CustomValidationException When one or more required fields are missing.
	 */
	private function validateRequiredFields(array $data, string $group, array $fields, string $message): void {
		$errors = [];
		foreach ($fields as $field) {
			if (isset($data[$field]) === false) {
				$errors[] = ['name' => "$group.$field", 'code' => 'required', 'reason' => "Het veld $field is verplicht"];
			}
		}

		if (count($errors) > 0) {
			throw new CustomValidationException($message, $errors);
		}
	}//end validateRequiredFields()
}//end class
