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

	private \OCA\OpenRegister\Service\ObjectService $objectService;

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
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-005
	 */
	public function checkProductenOfDiensten(ObjectEntity $case): void {
		$arr = $case->jsonSerialize();

		if (is_array($arr['productenOfDiensten'] ?? null) === false) {
			// No producten/diensten configured on the zaak; nothing to validate.
			return;
		}

		$caseTypeUrl = $arr['zaaktype'] ?? null;
		if ($caseTypeUrl === null || $caseTypeUrl === '') {
			// Without a zaaktype there is no reference set to validate against.
			return;
		}

		$ztId = explode('/', $caseTypeUrl);
		$this->objectService->clearCurrents();
		$caseType = $this->objectService->find(end($ztId));

		$allowed = $caseType->jsonSerialize()['productenOfDiensten'] ?? [];
		if (is_array($allowed) === false) {
			$allowed = [];
		}

		if (array_diff($arr['productenOfDiensten'], $allowed) !== []) {
			$this->throwValidationError('productenOfDiensten', 'invalid-products-services', 'Producten niet aanwezig op zaaktype');
		}
	}//end checkProductenOfDiensten()

	private function throwValidationError(string $name, string $code, string $reason): void {
		throw new CustomValidationException($reason, [['name' => $name, 'code' => $code, 'reason' => $reason]]);
	}//end throwValidationError()

	/**
	 * ZRC-022: Check archive prerequisites.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-005
	 */
	public function checkArchivePrerequisites(ObjectEntity $case): void {
		$arr = $this->objectService->renderEntity($case);

		// A zaak that is not (yet) flagged for archiving has no archive prerequisites.
		// When the archive lifecycle has not started, archiefstatus is either absent
		// (e.g. on a fresh create where the form does not expose the field) or the
		// explicit 'nog_te_archiveren' value — in both cases there is nothing to enforce.
		$archiefstatus = $arr['archiefstatus'] ?? null;
		if ($archiefstatus === null || $archiefstatus === '' || $archiefstatus === 'nog_te_archiveren') {
			return;
		}

		$this->validateEioStatuses($arr);

		if (($arr['archiefnominatie'] ?? null) === null) {
			$this->throwValidationError('archiefnominatie', 'archiefnominatie-not-set', 'De archiefnominatie moet geset zijn');
		}

		if (($arr['archiefactiedatum'] ?? null) === null) {
			$this->throwValidationError('archiefactiedatum', 'archiefactiedatum-not-set', 'De archiefactiedatum moet geset zijn');
		}
	}//end checkArchivePrerequisites()

	/**
	 * ZRC-012: Check verlenging and opschorting parameters.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-005
	 */
	public function checkGegevensgroepen(ObjectEntity $case): void {
		$arr = $case->jsonSerialize();

		if (($arr['verlenging'] ?? null) !== null) {
			$this->validateRequiredFields($arr['verlenging'], 'verlenging', ['reden', 'duur'], 'Verlenging is incorrect');
		}

		if (($arr['opschorting'] ?? null) !== null) {
			$this->validateRequiredFields($arr['opschorting'], 'opschorting', ['indicatie', 'reden'], 'Opschorting is incorrect');
		}
	}//end checkGegevensgroepen()

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
			$this->throwValidationError('zaakinformatieobjecten', 'informatieobject-status-not-set', 'Alle informatieobjecten moeten status gearchiveerd hebben.');
		}
	}//end validateEioStatuses()

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
