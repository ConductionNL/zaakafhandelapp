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

	/**
	 * The OpenRegister object service used to look up related objects.
	 *
	 * @var \OCA\OpenRegister\Service\ObjectService
	 */
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
	 * @param array $zaakArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 * @param string $brcRegister The BRC register slug
	 * @param string $besluitSchema The besluit schema slug
	 *
	 * @return string|null The calculated archive action date, or null
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-004
	 */
	public function calculateArchiveDate(
		?string $afleidingswijze,
		array $zaakArray,
		array $resultaattypeArray,
		string $brcRegister,
		string $besluitSchema,
	): ?string {
		// Per ZGW spec and Archiefwet: brondatum + resultaattype.archiefactietermijn = archiefactiedatum.
		// 'termijn' already includes the interval via procestermijn; all other branches derive only
		// the brondatum and must still add archiefactietermijn. (C3/C4 fix)
		$brondatum = match ($afleidingswijze) {
			'afgehandeld' => $zaakArray['einddatum'],
			'hoofdzaak' => $this->calculateFromHoofdzaak(zaakArray: $zaakArray),
			'eigenschap' => $this->calculateFromEigenschap(zaakArray: $zaakArray, resultaattypeArray: $resultaattypeArray),
			'ander_datumkenmerk' => null,
			'termijn' => $this->calculateFromTermijn(zaakArray: $zaakArray, resultaattypeArray: $resultaattypeArray),
			'ingangsdatum_besluit' => $this->calculateFromBesluit(
				zaakArray: $zaakArray,
				dateField: 'ingangsdatum',
				brcRegister: $brcRegister,
				besluitSchema: $besluitSchema
			),
			'vervaldatum_besluit' => $this->calculateFromBesluit(
				zaakArray: $zaakArray,
				dateField: 'vervaldatum',
				brcRegister: $brcRegister,
				besluitSchema: $besluitSchema
			),
			'gerelateerde_zaak' => $this->calculateFromGerelateerdeZaak(zaakArray: $zaakArray),
			'zaakobject' => $this->calculateFromZaakobject(zaakArray: $zaakArray, resultaattypeArray: $resultaattypeArray),
			default => $this->handleUnknownAfleidingswijze(afleidingswijze: $afleidingswijze),
		};

		// 'termijn' already adds procestermijn inside calculateFromTermijn; for all other afleidingswijze
		// we must add the resultaattype's archiefactietermijn on top of the derived brondatum.
		if ($afleidingswijze === 'termijn' || $brondatum === null) {
			return $brondatum;
		}

		return $this->applyArchiefactietermijn(brondatum: $brondatum, resultaattypeArray: $resultaattypeArray);
	}//end calculateArchiveDate()

	/**
	 * Calculate archive date from hoofdzaak.
	 *
	 * @param array $zaakArray The zaak data array
	 *
	 * @return string|null The archive date
	 */
	private function calculateFromHoofdzaak(array $zaakArray): ?string {
		// Guard: hoofdzaak may be null when this zaak is not a deelzaak (#278).
		if (empty($zaakArray['hoofdzaak']) === true) {
			return null;
		}

		$hoofdzaakId = explode('/', $zaakArray['hoofdzaak']);
		$hoofdzaakId = end($hoofdzaakId);
		$this->objectService->clearCurrents();
		$hoofdzaak = $this->objectService->find($hoofdzaakId);

		return $hoofdzaak->jsonSerialize()['einddatum'] ?? null;
	}//end calculateFromHoofdzaak()

	/**
	 * Calculate archive date from eigenschap.
	 *
	 * @param array $zaakArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 *
	 * @return string|null The archive date
	 */
	private function calculateFromEigenschap(array $zaakArray, array $resultaattypeArray): ?string {
		$eigenschap = $resultaattypeArray['brondatumArchiefprocedure']['datumkenmerk'] ?? null;
		$eigenschapIds = array_map(
			function ($item) {
				$exploded = explode('/', $item);
				return end($exploded);
			},
			$zaakArray['eigenschappen']
		);

		// Guard: an empty ids array would cause findAll to return ALL objects (M1).
		// Return null immediately when the zaak has no eigenschappen.
		if (empty($eigenschapIds) === true) {
			return null;
		}

		$this->objectService->clearCurrents();
		$eigenschappen = $this->objectService->findAll(['ids' => $eigenschapIds]);
		$eigenschapObjects = array_filter(
			$eigenschappen,
			function (ObjectEntity $eigenschapObject) use ($eigenschap) {
				return $eigenschapObject->jsonSerialize()['naam'] === $eigenschap;
			}
		);
		$eigenschapObject = array_shift($eigenschapObjects);

		// Guard: array_shift returns null when no matching eigenschap was found (#278).
		if ($eigenschapObject === null) {
			return null;
		}

		return $eigenschapObject->jsonSerialize()['waarde'] ?? null;
	}//end calculateFromEigenschap()

	/**
	 * Calculate archive date from termijn.
	 *
	 * @param array $zaakArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 *
	 * @return string The archive date
	 */
	private function calculateFromTermijn(array $zaakArray, array $resultaattypeArray): string {
		$date = new DateTime($zaakArray['einddatum']);
		$interval = new DateInterval($resultaattypeArray['brondatumArchiefprocedure']['procestermijn']);

		return $date->add($interval)->format('Y-m-d');
	}//end calculateFromTermijn()

	/**
	 * Calculate archive date from besluit ingangsdatum or vervaldatum.
	 *
	 * @param array $zaakArray The zaak data array
	 * @param string $dateField The date field to use ('ingangsdatum' or 'vervaldatum')
	 * @param string $brcRegister The BRC register slug
	 * @param string $besluitSchema The besluit schema slug
	 *
	 * @return string|null The archive date
	 */
	private function calculateFromBesluit(
		array $zaakArray,
		string $dateField,
		string $brcRegister,
		string $besluitSchema,
	): ?string {
		// OpenRegister findAll expects numeric IDs for register/schema filters, not slugs.
		// Resolve the slugs to their database IDs first (fixes #277).
		$registerId = $this->registerMapper->find($brcRegister)->getId();
		$schemaId = $this->schemaMapper->find($besluitSchema)->getId();

		$this->objectService->clearCurrents();
		$besluiten = $this->objectService->findAll(
			[
				'filters' => [
					'zaak' => $zaakArray['url'],
					'register' => $registerId,
					'schema' => $schemaId,
				],
			]
		);

		$mapped = array_map(
			function (ObjectEntity $besluit) use ($dateField) {
				return $besluit->jsonSerialize()[$dateField] ?? null;
			},
			$besluiten
		);
		$data = array_filter($mapped);

		// The max() of an empty array is false, so return null when there are no dated besluiten.
		if (empty($data) === true) {
			return null;
		}

		return max($data);
	}//end calculateFromBesluit()

	/**
	 * Calculate brondatum from gerelateerde_zaak.
	 *
	 * Per VNG ZGW: use the einddatum of the related zaak referenced by the resultaattype's
	 * brondatumArchiefprocedure.objecttype context.
	 *
	 * @param array $zaakArray The zaak data array
	 *
	 * @return string|null The brondatum
	 */
	private function calculateFromGerelateerdeZaak(array $zaakArray): ?string {
		$relevanteAndereZaken = $zaakArray['relevanteAndereZaken'] ?? [];
		if (empty($relevanteAndereZaken) === true) {
			return null;
		}

		$dates = [];
		foreach ($relevanteAndereZaken as $relatie) {
			if (is_array($relatie) === true) {
				$zaakUrl = $relatie['url'] ?? $relatie;
			} else {
				$zaakUrl = $relatie;
			}

			$zaakId = explode('/', rtrim((string)$zaakUrl, '/'));
			$zaakId = end($zaakId);
			if (empty($zaakId) === true) {
				continue;
			}

			$this->objectService->clearCurrents();
			try {
				$relatedZaak = $this->objectService->find($zaakId);
				$einddatum = $relatedZaak->jsonSerialize()['einddatum'] ?? null;
				if ($einddatum !== null) {
					$dates[] = $einddatum;
				}
			} catch (\Exception $e) {
				// Related zaak not found; skip it.
			}
		}//end foreach

		if (empty($dates) === true) {
			return null;
		}

		return max($dates);
	}//end calculateFromGerelateerdeZaak()

	/**
	 * Calculate brondatum from zaakobject.
	 *
	 * Per VNG ZGW: use the datumkenmerk field value from the zaakobject identified by
	 * the resultaattype's brondatumArchiefprocedure.objecttype.
	 *
	 * @param array $zaakArray The zaak data array
	 * @param array $resultaattypeArray The resultaattype data array
	 *
	 * @return string|null The brondatum
	 */
	private function calculateFromZaakobject(array $zaakArray, array $resultaattypeArray): ?string {
		$datumkenmerk = $resultaattypeArray['brondatumArchiefprocedure']['datumkenmerk'] ?? null;
		$objecttype = $resultaattypeArray['brondatumArchiefprocedure']['objecttype'] ?? null;
		$zaakobjecten = $zaakArray['zaakobjecten'] ?? [];

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

				$datum = $zaakobjectData['object'][$datumkenmerk] ?? $zaakobjectData[$datumkenmerk] ?? null;
				if ($datum !== null) {
					return (string)$datum;
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
		$termijn = $resultaattypeArray['archiefactietermijn'] ?? null;

		if ($termijn === null || $termijn === '') {
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
			$interval = new DateInterval($termijn);
			return $date->add($interval)->format('Y-m-d');
		} catch (\Exception $e) {
			$this->logger->warning(
				'ZGWArchiveDateService: could not apply archiefactietermijn; using brondatum as archiefactiedatum',
				[
					'brondatum' => $brondatum,
					'termijn' => $termijn,
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
