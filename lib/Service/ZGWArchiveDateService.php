<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateInterval;
use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\SchemaMapper;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Service for calculating archive dates based on afleidingswijze.
 *
 * Extracted from ZGWLogicService to reduce class complexity.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZGWArchiveDateService {

	private \OCA\OpenRegister\Service\ObjectService $objectService;

	/**
	 * Constructor for ZGWArchiveDateService.
	 *
	 * @param ObjectMapperService $mapperService The object service wrapper
	 * @param RegisterMapper $registerMapper Mapper for resolving register slug → ID
	 * @param SchemaMapper $schemaMapper Mapper for resolving schema slug → ID
	 * @param LoggerInterface $logger Logger for unknown afleidingswijze warnings
	 *
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function __construct(
		ObjectMapperService $mapperService,
		private RegisterMapper $registerMapper,
		private SchemaMapper $schemaMapper,
		private LoggerInterface $logger,
	) {
		$objectService = $mapperService->getOpenRegisters();
		if ($objectService === null) {
			throw new RuntimeException('ZGWArchiveDateService requires the OpenRegister app to be installed and enabled.');
		}

		$this->objectService = $objectService;
	}//end __construct()

	/**
	 * Calculate the archive action date based on the afleidingswijze.
	 *
	 * @param string|null $afleidingswijze The derivation method
	 * @param array $caseArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 * @param string $brcRegister The BRC register slug
	 * @param string $decisionSchema The besluit schema slug
	 *
	 * @return string|null The calculated archive action date, or null
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-004
	 */
	public function calculateArchiveDate(
		?string $afleidingswijze,
		array $caseArray,
		array $resultaattypeArray,
		string $brcRegister,
		string $decisionSchema,
	): ?string {
		// Per ZGW spec and Archiefwet: brondatum + resultaattype.archiefactietermijn = archiefactiedatum.
		// 'termijn' already includes the interval via procestermijn; all other branches derive only
		// the brondatum and must still add archiefactietermijn. (C3/C4 fix)
		$brondatum = match ($afleidingswijze) {
			'afgehandeld' => $caseArray['einddatum'],
			'hoofdzaak' => $this->calculateFromHoofdzaak($caseArray),
			'eigenschap' => $this->calculateFromAttribute($caseArray, $resultaattypeArray),
			'ander_datumkenmerk' => null,
			'termijn' => $this->calculateFromTerm($caseArray, $resultaattypeArray),
			'ingangsdatum_besluit' => $this->calculateFromDecision($caseArray, 'ingangsdatum', $brcRegister, $decisionSchema),
			'vervaldatum_besluit' => $this->calculateFromDecision($caseArray, 'vervaldatum', $brcRegister, $decisionSchema),
			'gerelateerde_zaak' => $this->calculateFromGerelateerdeCase($caseArray),
			'zaakobject' => $this->calculateFromZaakobject($caseArray, $resultaattypeArray),
			default => $this->handleUnknownAfleidingswijze($afleidingswijze),
		};

		// 'termijn' already adds procestermijn inside calculateFromTermijn; for all other afleidingswijze
		// we must add the resultaattype's archiefactietermijn on top of the derived brondatum.
		if ($afleidingswijze === 'termijn' || $brondatum === null) {
			return $brondatum;
		}

		return $this->applyArchiefactietermijn($brondatum, $resultaattypeArray);
	}//end calculateArchiveDate()

	/**
	 * Calculate archive date from hoofdzaak.
	 *
	 * @param array $caseArray The zaak data array
	 *
	 * @return string|null The archive date
	 */
	private function calculateFromHoofdzaak(array $caseArray): ?string {
		// Guard: hoofdzaak may be null when this zaak is not a deelzaak (#278).
		if (empty($caseArray['hoofdzaak']) === true) {
			return null;
		}

		$hoofdzaakId = explode('/', $caseArray['hoofdzaak']);
		$hoofdzaakId = end($hoofdzaakId);
		$this->objectService->clearCurrents();
		$hoofdzaak = $this->objectService->find($hoofdzaakId);

		return $hoofdzaak->jsonSerialize()['einddatum'] ?? null;
	}//end calculateFromHoofdzaak()

	/**
	 * Calculate archive date from eigenschap.
	 *
	 * @param array $caseArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 *
	 * @return string|null The archive date
	 */
	private function calculateFromAttribute(array $caseArray, array $resultaattypeArray): ?string {
		$attribute = $resultaattypeArray['brondatumArchiefprocedure']['datumkenmerk'] ?? null;
		$attributeIds = array_map(
			function ($item) {
				$exploded = explode('/', $item);
				return end($exploded);
			},
			$caseArray['eigenschappen']
		);

		// Guard: an empty ids array would cause findAll to return ALL objects (M1).
		// Return null immediately when the zaak has no eigenschappen.
		if (empty($attributeIds) === true) {
			return null;
		}

		$this->objectService->clearCurrents();
		$attributes = $this->objectService->findAll(['ids' => $attributeIds]);
		$attributeObjects = array_filter(
			$attributes,
			function (ObjectEntity $attributeObject) use ($attribute) {
				return $attributeObject->jsonSerialize()['naam'] === $attribute;
			}
		);
		$attributeObject = array_shift($attributeObjects);

		// Guard: array_shift returns null when no matching eigenschap was found (#278).
		if ($attributeObject === null) {
			return null;
		}

		return $attributeObject->jsonSerialize()['waarde'] ?? null;
	}//end calculateFromEigenschap()

	/**
	 * Calculate archive date from termijn.
	 *
	 * @param array $caseArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 *
	 * @return string The archive date
	 */
	private function calculateFromTerm(array $caseArray, array $resultaattypeArray): string {
		$date = new DateTime($caseArray['einddatum']);
		$interval = new DateInterval($resultaattypeArray['brondatumArchiefprocedure']['procestermijn']);

		return $date->add($interval)->format('Y-m-d');
	}//end calculateFromTermijn()

	/**
	 * Calculate archive date from besluit ingangsdatum or vervaldatum.
	 *
	 * @param array $caseArray The zaak data array
	 * @param string $dateField The date field to use ('ingangsdatum' or 'vervaldatum')
	 * @param string $brcRegister The BRC register slug
	 * @param string $decisionSchema The besluit schema slug
	 *
	 * @return string|null The archive date
	 */
	private function calculateFromDecision(
		array $caseArray,
		string $dateField,
		string $brcRegister,
		string $decisionSchema,
	): ?string {
		// OpenRegister findAll expects numeric IDs for register/schema filters, not slugs.
		// Resolve the slugs to their database IDs first (fixes #277).
		$registerId = $this->registerMapper->find($brcRegister)->getId();
		$schemaId = $this->schemaMapper->find($decisionSchema)->getId();

		$this->objectService->clearCurrents();
		$decisions = $this->objectService->findAll(
			[
				'filters' => [
					'zaak' => $caseArray['url'],
					'register' => $registerId,
					'schema' => $schemaId,
				],
			]
		);

		$mapped = array_map(
			function (ObjectEntity $decision) use ($dateField) {
				return $decision->jsonSerialize()[$dateField] ?? null;
			},
			$decisions
		);
		$data = array_filter($mapped);

		// max([]) returns false; return null when there are no dated besluiten.
		return empty($data) === false ? max($data) : null;
	}//end calculateFromBesluit()

	/**
	 * Calculate brondatum from gerelateerde_zaak.
	 *
	 * Per VNG ZGW: use the einddatum of the related zaak referenced by the resultaattype's
	 * brondatumArchiefprocedure.objecttype context.
	 *
	 * @param array $caseArray The zaak data array
	 *
	 * @return string|null The brondatum
	 */
	private function calculateFromGerelateerdeCase(array $caseArray): ?string {
		$relevanteAndereCases = $caseArray['relevanteAndereZaken'] ?? [];
		if (empty($relevanteAndereCases) === true) {
			return null;
		}

		$dates = [];
		foreach ($relevanteAndereCases as $relationship) {
			$caseUrl = is_array($relationship) ? ($relationship['url'] ?? $relationship) : $relationship;
			$caseId = explode('/', rtrim((string)$caseUrl, '/'));
			$caseId = end($caseId);
			if (empty($caseId) === true) {
				continue;
			}

			$this->objectService->clearCurrents();
			try {
				$relatedCase = $this->objectService->find($caseId);
				$endDate = $relatedCase->jsonSerialize()['einddatum'] ?? null;
				if ($endDate !== null) {
					$dates[] = $endDate;
				}
			} catch (\Exception $e) {
				// Related zaak not found; skip it.
			}
		}//end foreach

		return empty($dates) === false ? max($dates) : null;
	}//end calculateFromGerelateerdeZaak()

	/**
	 * Calculate brondatum from zaakobject.
	 *
	 * Per VNG ZGW: use the datumkenmerk field value from the zaakobject identified by
	 * the resultaattype's brondatumArchiefprocedure.objecttype.
	 *
	 * @param array $caseArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 *
	 * @return string|null The brondatum
	 */
	private function calculateFromZaakobject(array $caseArray, array $resultaattypeArray): ?string {
		$datumkenmerk = $resultaattypeArray['brondatumArchiefprocedure']['datumkenmerk'] ?? null;
		$objecttype = $resultaattypeArray['brondatumArchiefprocedure']['objecttype'] ?? null;
		$zaakobjecten = $caseArray['zaakobjecten'] ?? [];

		if ($datumkenmerk === null || empty($zaakobjecten) === true) {
			return null;
		}

		foreach ($zaakobjecten as $zaakobjectRef) {
			$zaakobjectId = explode('/', rtrim((string)$zaakobjectRef, '/'));
			$zaakobjectId = end($zaakobjectId);
			if (empty($zaakobjectId) === true) {
				continue;
			}

			$this->objectService->clearCurrents();
			try {
				$zaakobject = $this->objectService->find($zaakobjectId);
				$zaakobjectData = $zaakobject->jsonSerialize();
				if ($objecttype !== null && ($zaakobjectData['objectType'] ?? null) !== $objecttype) {
					continue;
				}

				$date = $zaakobjectData['object'][$datumkenmerk] ?? $zaakobjectData[$datumkenmerk] ?? null;
				if ($date !== null) {
					return (string)$date;
				}
			} catch (\Exception $e) {
				// Zaakobject not found; skip it.
			}
		}//end foreach

		return null;
	}//end calculateFromZaakobject()

	/**
	 * Add the resultaattype's archiefactietermijn to the brondatum.
	 *
	 * Per Archiefwet/ZGW: archiefactiedatum = brondatum + archiefactietermijn.
	 * If archiefactietermijn is absent or invalid, the brondatum itself is returned so
	 * that a date is always set (rather than silently dropping the archive date).
	 *
	 * @param string $brondatum ISO-8601 date string (Y-m-d)
	 * @param array $resultaattypeArray The resultaattype data array
	 *
	 * @return string The archive action date (Y-m-d)
	 */
	private function applyArchiefactietermijn(string $brondatum, array $resultaattypeArray): string {
		$term = $resultaattypeArray['archiefactietermijn'] ?? null;

		if ($term === null || $term === '') {
			// No termijn defined on the resultaattype; fall back to brondatum as-is.
			$this->logger->warning(
				'ZGWArchiveDateService: resultaattype has no archiefactietermijn; using brondatum as archiefactiedatum',
				[
					'brondatum' => $brondatum,
					'resultaattype_id' => $resultaattypeArray['url'] ?? $resultaattypeArray['id'] ?? 'unknown',
				]
			);
			return $brondatum;
		}

		try {
			$date = new DateTime($brondatum);
			$interval = new DateInterval($term);
			return $date->add($interval)->format('Y-m-d');
		} catch (\Exception $e) {
			$this->logger->warning(
				'ZGWArchiveDateService: could not apply archiefactietermijn; using brondatum as archiefactiedatum',
				[
					'brondatum' => $brondatum,
					'termijn' => $term,
					'error' => $e->getMessage(),
				]
			);
			return $brondatum;
		}
	}//end applyArchiefactietermijn()

	/**
	 * Handle an unknown afleidingswijze by logging a warning and returning null.
	 *
	 * @param string|null $afleidingswijze The unknown value
	 *
	 * @return null Always returns null
	 */
	private function handleUnknownAfleidingswijze(?string $afleidingswijze): null {
		$this->logger->warning(
			'ZGWArchiveDateService: unknown afleidingswijze encountered; archiefactiedatum will not be set',
			['afleidingswijze' => $afleidingswijze]
		);
		return null;
	}//end handleUnknownAfleidingswijze()
}//end class
