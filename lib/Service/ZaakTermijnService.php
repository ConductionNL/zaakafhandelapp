<?php

/**
 * Zaak Afhandel App — behandeltermijn derivation service.
 *
 * Derives the statutory and service deadline fields of a zaak from its
 * zaaktype on creation: `uiterlijkeEinddatumAfdoening` from
 * `startdatum + zaaktype.doorlooptijd` (the wettelijke afhandeltermijn) and
 * `einddatumGepland` from `startdatum + zaaktype.servicenorm` (the
 * streeftermijn). Client-supplied values are never overridden.
 *
 * @category Service
 * @package  OCA\ZaakAfhandelApp\Service
 *
 * @author    Conduction <info@conduction.nl>
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Service;

use DateInterval;
use DateTimeImmutable;
use OCA\OpenRegister\Db\ObjectEntity;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Derives the termijn fields of a zaak from its zaaktype on creation (REQ-001).
 */
class ZaakTermijnService {

	/**
	 * The OpenRegister object service used to resolve the linked zaaktype.
	 *
	 * @var \OCA\OpenRegister\Service\ObjectService
	 */
	private \OCA\OpenRegister\Service\ObjectService $objectService;

	/**
	 * Constructor.
	 *
	 * @param ObjectMapperService $mapperService The OpenRegister mapper service.
	 * @param ZGWRegistryService $registry The schema/endpoint registry.
	 * @param LoggerInterface $logger The logger.
	 */
	public function __construct(
		ObjectMapperService $mapperService,
		private readonly ZGWRegistryService $registry,
		private readonly LoggerInterface $logger,
	) {
		$objectService = $mapperService->getOpenRegisters();
		if ($objectService === null) {
			throw new RuntimeException('ZaakTermijnService requires the OpenRegister app to be installed and enabled.');
		}

		$this->objectService = $objectService;
	}//end __construct()

	/**
	 * Derive the termijn fields on a freshly-created zaak (REQ-001).
	 *
	 * Mutates the zaak in place so the surrounding ObjectCreating write persists
	 * the derived dates. Never overrides client-supplied values; skips silently
	 * when the zaaktype or its terms are missing.
	 *
	 * @param ObjectEntity $zaak The zaak being created.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/zaak-termijn-monitoring/spec.md#REQ-001
	 */
	public function deriveTermijnen(ObjectEntity $zaak): void {
		$arr = $zaak->jsonSerialize();

		$needsUiterste = (string)($arr['uiterlijkeEinddatumAfdoening'] ?? '') === '';
		$needsGepland = (string)($arr['einddatumGepland'] ?? '') === '';

		if ($needsUiterste === false && $needsGepland === false) {
			// Both client-supplied; nothing to derive.
			return;
		}

		$zaaktype = $this->resolveZaaktype(arr: $arr);
		if ($zaaktype === []) {
			return;
		}

		$base = $this->resolveBaseDate(arr: $arr);
		if ($base === null) {
			return;
		}

		// Both termijnen derive the same way - a zaaktype term added to the base
		// date - so they are driven from one table rather than two copies of the
		// same block: [target field => [zaaktype term, whether it still needs one]].
		$derivations = [
			'uiterlijkeEinddatumAfdoening' => ['doorlooptijd', $needsUiterste],
			'einddatumGepland' => ['servicenorm', $needsGepland],
		];

		$changed = false;
		foreach ($derivations as $field => [$term, $needed]) {
			if ($needed === false) {
				continue;
			}

			$date = $this->termijnDate(duration: (string)($zaaktype[$term] ?? ''), base: $base);
			if ($date === null) {
				continue;
			}

			$arr[$field] = $date;
			$changed = true;
		}

		if ($changed === true) {
			$zaak->setObject($arr);
		}
	}//end deriveTermijnen()

	/**
	 * Add a zaaktype term to the base date.
	 *
	 * @param string $duration The zaaktype term as an ISO 8601 duration.
	 * @param DateTimeImmutable $base The derivation base date.
	 *
	 * @return ?string The derived date as Y-m-d, or null when the term is absent
	 *                 or unparsable.
	 */
	private function termijnDate(string $duration, DateTimeImmutable $base): ?string {
		$days = $this->durationToDays(duration: $duration);
		if ($days === null || $days <= 0) {
			return null;
		}

		return $base->add(new DateInterval('P' . $days . 'D'))->format('Y-m-d');
	}//end termijnDate()

	/**
	 * Resolve the derivation base date: startdatum, else registratiedatum.
	 *
	 * @param array<string,mixed> $arr The zaak payload.
	 *
	 * @return ?DateTimeImmutable The base date, or null when neither is parseable.
	 */
	private function resolveBaseDate(array $arr): ?DateTimeImmutable {
		foreach (['startdatum', 'registratiedatum'] as $field) {
			$value = (string)($arr[$field] ?? '');
			if ($value === '') {
				continue;
			}

			try {
				return new DateTimeImmutable($value);
			} catch (\Exception $e) {
				continue;
			}
		}

		return null;
	}//end resolveBaseDate()

	/**
	 * Resolve the linked zaaktype as an array, or an empty array.
	 *
	 * @param array<string,mixed> $arr The zaak payload.
	 *
	 * @return array<string,mixed> The zaaktype payload.
	 */
	private function resolveZaaktype(array $arr): array {
		$zaaktypeUrl = ($arr['zaaktype'] ?? null);
		if ($zaaktypeUrl === null || $zaaktypeUrl === '') {
			return [];
		}

		try {
			$this->objectService->clearCurrents();
			$zaaktype = $this->objectService->find($this->registry->getObjectIdByEndpointUrl((string)$zaaktypeUrl));
			return $zaaktype->jsonSerialize();
		} catch (\Throwable $e) {
			$this->logger->info('ZaakTermijnService: could not resolve zaaktype for derivation', ['error' => $e->getMessage()]);
			return [];
		}
	}//end resolveZaaktype()

	/**
	 * Parse an ISO 8601 duration (or plain day count) into a number of days.
	 *
	 * Accepts "P56D", "P8W", "P2M" (≈60d), "P1Y" (≈365d) and plain integers.
	 * Returns null when unparsable (the field is then skipped + logged).
	 *
	 * @param string $duration The duration string.
	 *
	 * @return ?integer The duration in days, or null.
	 */
	private function durationToDays(string $duration): ?int {
		$duration = trim($duration);
		if ($duration === '') {
			return null;
		}

		if (ctype_digit($duration) === true) {
			return (int)$duration;
		}

		if (preg_match('/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)W)?(?:(\d+)D)?$/', $duration, $matches) !== 1) {
			$this->logger->warning('ZaakTermijnService: unparsable duration, skipping derivation', ['duration' => $duration]);
			return null;
		}

		if (($matches[1] ?? '') === '' && ($matches[2] ?? '') === '' && ($matches[3] ?? '') === '' && ($matches[4] ?? '') === '') {
			return null;
		}

		$years = (int)($matches[1] ?? 0);
		$months = (int)($matches[2] ?? 0);
		$weeks = (int)($matches[3] ?? 0);
		$days = (int)($matches[4] ?? 0);

		return ($years * 365) + ($months * 30) + ($weeks * 7) + $days;
	}//end durationToDays()
}//end class
