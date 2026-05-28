<?php

namespace OCA\ZaakAfhandelApp\Service;

use DateInterval;
use DateTime;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\SchemaMapper;

/**
 * Service for calculating archive dates based on afleidingswijze.
 *
 * Extracted from ZGWLogicService to reduce class complexity.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ZGWArchiveDateService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    /**
     * Constructor for ZGWArchiveDateService.
     *
     * @param ObjectMapperService $mapperService   The object service wrapper
     * @param RegisterMapper      $registerMapper  Mapper for resolving register slug → ID
     * @param SchemaMapper        $schemaMapper    Mapper for resolving schema slug → ID
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(
        ObjectMapperService $mapperService,
        private RegisterMapper $registerMapper,
        private SchemaMapper $schemaMapper,
    ) {
        $objectService = $mapperService->getOpenRegisters();
        if ($objectService === null) {
            throw new \RuntimeException('ZGWArchiveDateService requires the OpenRegister app to be installed and enabled.');
        }

        $this->objectService = $objectService;
    }//end __construct()

    /**
     * Calculate the archive action date based on the afleidingswijze.
     *
     * @param string|null $afleidingswijze    The derivation method
     * @param array       $zaakArray          The zaak data array
     * @param array       $resultaattypeArray The resultaattype data array
     * @param string      $brcRegister        The BRC register slug
     * @param string      $besluitSchema      The besluit schema slug
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
        return match ($afleidingswijze) {
            'afgehandeld' => $zaakArray['einddatum'],
            'hoofdzaak' => $this->calculateFromHoofdzaak($zaakArray),
            'eigenschap' => $this->calculateFromEigenschap($zaakArray, $resultaattypeArray),
            'ander_datumkenmerk' => null,
            'termijn' => $this->calculateFromTermijn($zaakArray, $resultaattypeArray),
            'ingangsdatum_besluit' => $this->calculateFromBesluit($zaakArray, 'ingangsdatum', $brcRegister, $besluitSchema),
            'vervaldatum_besluit' => $this->calculateFromBesluit($zaakArray, 'vervaldatum', $brcRegister, $besluitSchema),
            default => null,
        };
    }//end calculateArchiveDate()

    /**
     * Calculate archive date from hoofdzaak.
     *
     * @param array $zaakArray The zaak data array
     *
     * @return string|null The archive date
     */
    private function calculateFromHoofdzaak(array $zaakArray): ?string
    {
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
     * @param array $zaakArray          The zaak data array
     * @param array $resultaattypeArray The resultaattype data array
     *
     * @return string|null The archive date
     */
    private function calculateFromEigenschap(array $zaakArray, array $resultaattypeArray): ?string
    {
        $eigenschap    = $resultaattypeArray['brondatumArchiefprocedure']['datumkenmerk'] ?? null;
        $eigenschapIds = array_map(
                function ($item) {
                    $exploded = explode('/', $item);
                    return end($exploded);
                },
                $zaakArray['eigenschappen']
                );
        $this->objectService->clearCurrents();
        $eigenschappen     = $this->objectService->findAll(['ids' => $eigenschapIds]);
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
     * @param array $zaakArray          The zaak data array
     * @param array $resultaattypeArray The resultaattype data array
     *
     * @return string The archive date
     */
    private function calculateFromTermijn(array $zaakArray, array $resultaattypeArray): string
    {
        $date     = new DateTime($zaakArray['einddatum']);
        $interval = new DateInterval($resultaattypeArray['brondatumArchiefprocedure']['procestermijn']);

        return $date->add($interval)->format('Y-m-d');
    }//end calculateFromTermijn()

    /**
     * Calculate archive date from besluit ingangsdatum or vervaldatum.
     *
     * @param array  $zaakArray     The zaak data array
     * @param string $dateField     The date field to use ('ingangsdatum' or 'vervaldatum')
     * @param string $brcRegister   The BRC register slug
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
        $schemaId   = $this->schemaMapper->find($besluitSchema)->getId();

        $this->objectService->clearCurrents();
        $besluiten = $this->objectService->findAll(
                [
                    'filters' => [
                        'zaak'     => $zaakArray['url'],
                        'register' => $registerId,
                        'schema'   => $schemaId,
                    ],
                ]
                );

        $data = array_filter(array_map(
            function (ObjectEntity $besluit) use ($dateField) {
                return $besluit->jsonSerialize()[$dateField] ?? null;
            },
            $besluiten
        ));

        // max([]) returns false; return null when there are no dated besluiten.
        return empty($data) === false ? max($data) : null;
    }//end calculateFromBesluit()
}//end class
