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
class ZaakTermijnService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    /**
     * Constructor.
     *
     * @param ObjectMapperService $mapperService The OpenRegister mapper service.
     * @param ZGWRegistryService  $registry      The schema/endpoint registry.
     * @param LoggerInterface     $logger        The logger.
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
     *
     * Complexity is one guard per precondition described above (zaaktype
     * present, each termijn present, each client value absent before deriving).
     * The method deliberately never overrides a client-supplied value, and each
     * of those checks is a separate branch.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function deriveTermijnen(ObjectEntity $zaak): void
    {
        $arr = $zaak->jsonSerialize();

        $needsUiterste = (string) ($arr['uiterlijkeEinddatumAfdoening'] ?? '') === '';
        $needsGepland  = (string) ($arr['einddatumGepland'] ?? '') === '';

        if ($needsUiterste === false && $needsGepland === false) {
            // Both client-supplied; nothing to derive.
            return;
        }

        $zaaktype = $this->resolveZaaktype($arr);
        if ($zaaktype === []) {
            return;
        }

        $base = $this->resolveBaseDate($arr);
        if ($base === null) {
            return;
        }

        $changed = false;

        if ($needsUiterste === true) {
            $days = $this->durationToDays((string) ($zaaktype['doorlooptijd'] ?? ''));
            if ($days !== null && $days > 0) {
                $arr['uiterlijkeEinddatumAfdoening'] = $base->add(new DateInterval('P'.$days.'D'))->format('Y-m-d');
                $changed = true;
            }
        }

        if ($needsGepland === true) {
            $days = $this->durationToDays((string) ($zaaktype['servicenorm'] ?? ''));
            if ($days !== null && $days > 0) {
                $arr['einddatumGepland'] = $base->add(new DateInterval('P'.$days.'D'))->format('Y-m-d');
                $changed = true;
            }
        }

        if ($changed === true) {
            $zaak->setObject($arr);
        }
    }//end deriveTermijnen()

    /**
     * Resolve the derivation base date: startdatum, else registratiedatum.
     *
     * @param array<string,mixed> $arr The zaak payload.
     *
     * @return ?DateTimeImmutable The base date, or null when neither is parseable.
     */
    private function resolveBaseDate(array $arr): ?DateTimeImmutable
    {
        foreach (['startdatum', 'registratiedatum'] as $field) {
            $value = (string) ($arr[$field] ?? '');
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
    private function resolveZaaktype(array $arr): array
    {
        $zaaktypeUrl = ($arr['zaaktype'] ?? null);
        if ($zaaktypeUrl === null || $zaaktypeUrl === '') {
            return [];
        }

        try {
            $this->objectService->clearCurrents();
            $zaaktype = $this->objectService->find($this->registry->getObjectIdByEndpointUrl((string) $zaaktypeUrl));
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
    private function durationToDays(string $duration): ?int
    {
        $duration = trim($duration);
        if ($duration === '') {
            return null;
        }

        if (ctype_digit($duration) === true) {
            return (int) $duration;
        }

        if (preg_match('/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)W)?(?:(\d+)D)?$/', $duration, $parts) !== 1) {
            $this->logger->warning('ZaakTermijnService: unparsable duration, skipping derivation', ['duration' => $duration]);
            return null;
        }

        if (($parts[1] ?? '') === '' && ($parts[2] ?? '') === '' && ($parts[3] ?? '') === '' && ($parts[4] ?? '') === '') {
            return null;
        }

        $years  = (int) ($parts[1] ?? 0);
        $months = (int) ($parts[2] ?? 0);
        $weeks  = (int) ($parts[3] ?? 0);
        $days   = (int) ($parts[4] ?? 0);

        return ($years * 365) + ($months * 30) + ($weeks * 7) + $days;
    }//end durationToDays()
}//end class
