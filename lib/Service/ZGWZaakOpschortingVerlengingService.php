<?php

/**
 * Zaak Afhandel App — opschorting (suspension) & verlenging (extension) service.
 *
 * Implements the two Awb instruments that shift a running beslistermijn:
 *  - opschorting (Awb art. 4:15): the clock stands still while the zaak is
 *    suspended; on resume both deadline fields shift forward by the elapsed
 *    suspension duration;
 *  - verlenging (Awb art. 4:14, verdaging): a single-shot extension that shifts
 *    both deadline fields forward by an ISO 8601 duration.
 * Both are gated on the zaaktype policy switches
 * (opschortingEnAanhoudingMogelijk / verlengingMogelijk / verlengingstermijn).
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
use OCA\OpenRegister\Exception\CustomValidationException;
use RuntimeException;

/**
 * Applies opschorting/verlenging transitions to a zaak on update, gating on the
 * zaaktype policy and recalculating the termijn fields.
 *
 * Exceeds PHPMD's class-complexity threshold (58 vs 50): the branches are the
 * ZGW opschorting/verlenging rules themselves — each one a policy gate the spec
 * requires. Splitting them across classes would separate rules that must be
 * evaluated against the same before/after zaak snapshot.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ZGWZaakOpschortingVerlengingService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    /**
     * Constructor.
     *
     * @param ObjectMapperService $mapperService The OpenRegister mapper service.
     * @param ZGWRegistryService  $registry      The schema/endpoint registry.
     */
    public function __construct(
        ObjectMapperService $mapperService,
        private readonly ZGWRegistryService $registry,
    ) {
        $objectService = $mapperService->getOpenRegisters();
        if ($objectService === null) {
            throw new RuntimeException('ZGWZaakOpschortingVerlengingService requires the OpenRegister app to be installed and enabled.');
        }

        $this->objectService = $objectService;
    }//end __construct()

    /**
     * Detect and apply an opschorting/verlenging transition on a zaak update.
     *
     * Mutates the new zaak in place (deadline shifts, suspension-start
     * bookkeeping) so the surrounding ObjectUpdating write persists the result.
     * Raises a CustomValidationException — aborting the write — when a transition
     * is not allowed.
     *
     * @param ObjectEntity      $zaak    The zaak being updated (new state).
     * @param array<string,mixed>|null $oldZaak The previously persisted zaak state, or
     *                                  null to resolve it from the store by uuid.
     * @param DateTimeImmutable|null $now The reference "now" (injectable for tests).
     *
     * @return void
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-006
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-007
     */
    public function applyTransitions(ObjectEntity $zaak, ?array $oldZaak=null, ?DateTimeImmutable $now=null): void
    {
        $now = $now ?? new DateTimeImmutable();
        $new = $zaak->jsonSerialize();

        if ($oldZaak === null) {
            $oldZaak = $this->resolveOldZaak($zaak);
        }

        // Both handlers must run, so evaluate each before folding it in — the
        // first fold had a provably-false right-hand side.
        $changed = $this->handleOpschorting($new, $oldZaak, $now);
        $changed = ($this->handleVerlenging($new, $oldZaak) || $changed);

        if ($changed === true) {
            $zaak->setObject($new);
        }
    }//end applyTransitions()

    /**
     * Apply an opschorting suspend/resume transition (REQ-006).
     *
     * @param array<string,mixed> $new     The new zaak (mutated in place).
     * @param array<string,mixed> $old     The previously persisted zaak.
     * @param DateTimeImmutable   $now      The reference now.
     *
     * @return boolean True when the zaak was mutated.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-006
     */
    private function handleOpschorting(array &$new, array $old, DateTimeImmutable $now): bool
    {
        $newIndicatie = $this->isIndicatie($new['opschorting'] ?? null);
        $oldIndicatie = $this->isIndicatie($old['opschorting'] ?? null);

        // No transition.
        if ($newIndicatie === $oldIndicatie) {
            return false;
        }

        if ($newIndicatie === true) {
            // Suspend.
            $this->assertOpen($old, 'opschorting');
            if ($this->zaaktypeAllows($new, 'opschortingEnAanhoudingMogelijk') === false) {
                $this->fail('opschorting', 'opschorting-not-allowed', 'Het zaaktype staat opschorting niet toe');
            }

            $reden = (string) ($new['opschorting']['reden'] ?? '');
            if (trim($reden) === '') {
                $this->fail('opschorting.reden', 'required', 'Een reden voor opschorting is verplicht');
            }

            $opschorting = (array) $new['opschorting'];
            $opschorting['indicatie'] = true;
            // App-managed bookkeeping: ZGW has no field for the suspension start.
            $opschorting['_opschortingGestart'] = $now->format(DATE_ATOM);
            $new['opschorting'] = $opschorting;

            return true;
        }

        // Resume: shift the deadlines by the elapsed suspension duration.
        $startRaw = (string) ($old['opschorting']['_opschortingGestart'] ?? '');
        if ($startRaw !== '') {
            $start       = new DateTimeImmutable($startRaw);
            $elapsedDays = (int) $start->diff($now)->days;
            if ($elapsedDays > 0) {
                $this->shiftDeadlines($new, $elapsedDays);
            }
        }

        $opschorting = (array) ($new['opschorting'] ?? []);
        $opschorting['indicatie'] = false;
        // Keep the last reden for the record; clear the start bookkeeping.
        unset($opschorting['_opschortingGestart']);
        $new['opschorting'] = $opschorting;

        return true;
    }//end handleOpschorting()

    /**
     * Apply a verlenging transition (REQ-007).
     *
     * @param array<string,mixed> $new The new zaak (mutated in place).
     * @param array<string,mixed> $old The previously persisted zaak.
     *
     * @return boolean True when the zaak was mutated.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-007
     *
     * Complexity is one guard per ZGW verlenging precondition (policy allowed,
     * duration present and parseable, maximum not exceeded, termijn present).
     * Each must be checked before the next is meaningful, so they cannot be
     * flattened or reordered.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function handleVerlenging(array &$new, array $old): bool
    {
        $newVerlenging = ($new['verlenging'] ?? null);
        // Only act on a freshly-added verlenging.
        if (is_array($newVerlenging) === false || ($newVerlenging['duur'] ?? '') === '') {
            return false;
        }

        $oldHadVerlenging = is_array($old['verlenging'] ?? null) === true
            && (($old['verlenging']['duur'] ?? '') !== '');

        // The same persisted verlenging being re-saved unchanged is not a new
        // extension and must not shift the deadlines again.
        if ($oldHadVerlenging === true) {
            $this->fail('verlenging', 'verlenging-already-applied', 'De zaak is al verlengd (verdaging is eenmalig)');
        }

        $this->assertOpen($old, 'verlenging');

        if (($new['opschorting']['indicatie'] ?? false) === true) {
            $this->fail('verlenging', 'zaak-suspended', 'Een opgeschorte zaak kan niet worden verlengd');
        }

        if ($this->zaaktypeAllows($new, 'verlengingMogelijk') === false) {
            $this->fail('verlenging', 'verlenging-not-allowed', 'Het zaaktype staat verlenging niet toe');
        }

        $reden = (string) ($newVerlenging['reden'] ?? '');
        if (trim($reden) === '') {
            $this->fail('verlenging.reden', 'required', 'Een reden voor verlenging is verplicht');
        }

        $duurDays = $this->durationToDays((string) $newVerlenging['duur']);
        if ($duurDays === null || $duurDays <= 0) {
            $this->fail('verlenging.duur', 'invalid-duration', 'De duur is geen geldige ISO 8601 duur');
        }

        // Cap against the zaaktype's verlengingstermijn when configured.
        $maxDays = $this->zaaktypeMaxVerlengingDays($new);
        if ($maxDays !== null && $duurDays > $maxDays) {
            $this->fail('verlenging.duur', 'duration-exceeds-termijn', 'De duur overschrijdt de verlengingstermijn van het zaaktype');
        }

        $this->shiftDeadlines($new, $duurDays);

        return true;
    }//end handleVerlenging()

    /**
     * Shift einddatumGepland and uiterlijkeEinddatumAfdoening forward by N days.
     *
     * @param array<string,mixed> $zaak The zaak (mutated in place).
     * @param integer             $days The number of days to add.
     *
     * @return void
     */
    private function shiftDeadlines(array &$zaak, int $days): void
    {
        foreach (['einddatumGepland', 'uiterlijkeEinddatumAfdoening'] as $field) {
            $value = (string) ($zaak[$field] ?? '');
            if ($value === '') {
                continue;
            }

            try {
                $date = new DateTimeImmutable($value);
            } catch (\Exception $e) {
                continue;
            }

            $zaak[$field] = $date->add(new DateInterval('P'.$days.'D'))->format('Y-m-d');
        }
    }//end shiftDeadlines()

    /**
     * Resolve the previously persisted zaak state by uuid.
     *
     * @param ObjectEntity $zaak The new zaak.
     *
     * @return array<string,mixed> The old zaak state, or an empty array when not found.
     */
    private function resolveOldZaak(ObjectEntity $zaak): array
    {
        $uuid = $zaak->getUuid();
        if ($uuid === null || $uuid === '') {
            return [];
        }

        try {
            $this->objectService->clearCurrents();
            $old = $this->objectService->find($uuid);
            return $old->jsonSerialize();
        } catch (\Throwable $e) {
            return [];
        }
    }//end resolveOldZaak()

    /**
     * Whether an opschorting group has indicatie === true.
     *
     * @param mixed $opschorting The opschorting group.
     *
     * @return boolean True when suspended.
     */
    private function isIndicatie(mixed $opschorting): bool
    {
        if (is_array($opschorting) === false) {
            return false;
        }

        return ($opschorting['indicatie'] ?? false) === true;
    }//end isIndicatie()

    /**
     * Assert the zaak is open (no einddatum). Closed zaken refuse the transition.
     *
     * @param array<string,mixed> $zaak  The previously persisted zaak.
     * @param string              $group The group name for the error.
     *
     * @return void
     */
    private function assertOpen(array $zaak, string $group): void
    {
        $einddatum = ($zaak['einddatum'] ?? null);
        if ($einddatum !== null && $einddatum !== '') {
            $this->fail($group, 'zaak-closed', 'De zaak is gesloten');
        }
    }//end assertOpen()

    /**
     * Whether the zaak's zaaktype enables a boolean policy switch.
     *
     * The zaaktype switch is stored as a string ('true'/'1') on the entity.
     *
     * @param array<string,mixed> $zaak     The zaak (carries the zaaktype url).
     * @param string              $property The policy property name.
     *
     * @return boolean True when the policy is enabled.
     */
    private function zaaktypeAllows(array $zaak, string $property): bool
    {
        $zaaktype = $this->resolveZaaktype($zaak);
        $value    = ($zaaktype[$property] ?? null);

        return $value === true || $value === 'true' || $value === '1' || $value === 1;
    }//end zaaktypeAllows()

    /**
     * The zaaktype's verlengingstermijn expressed in days, or null when unset.
     *
     * @param array<string,mixed> $zaak The zaak.
     *
     * @return ?integer The max verlenging in days, or null.
     */
    private function zaaktypeMaxVerlengingDays(array $zaak): ?int
    {
        $zaaktype = $this->resolveZaaktype($zaak);
        $termijn  = (string) ($zaaktype['verlengingstermijn'] ?? '');
        if ($termijn === '') {
            return null;
        }

        return $this->durationToDays($termijn);
    }//end zaaktypeMaxVerlengingDays()

    /**
     * Resolve the linked zaaktype as an array, or an empty array.
     *
     * @param array<string,mixed> $zaak The zaak.
     *
     * @return array<string,mixed> The zaaktype payload.
     */
    private function resolveZaaktype(array $zaak): array
    {
        $zaaktypeUrl = ($zaak['zaaktype'] ?? null);
        if ($zaaktypeUrl === null || $zaaktypeUrl === '') {
            return [];
        }

        try {
            $this->objectService->clearCurrents();
            $zaaktype = $this->objectService->find($this->registry->getObjectIdByEndpointUrl((string) $zaaktypeUrl));
            return $zaaktype->jsonSerialize();
        } catch (\Throwable $e) {
            return [];
        }
    }//end resolveZaaktype()

    /**
     * Parse an ISO 8601 duration (or plain day count) into a number of days.
     *
     * Accepts forms like "P14D", "P2W", "P1M" (≈30d), "P1Y" (≈365d) and plain
     * integers ("14"). Returns null when unparsable.
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

        // Plain day count.
        if (ctype_digit($duration) === true) {
            return (int) $duration;
        }

        if (preg_match('/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)W)?(?:(\d+)D)?$/', $duration, $parts) !== 1) {
            return null;
        }

        // Reject an empty "P".
        if (($parts[1] ?? '') === '' && ($parts[2] ?? '') === '' && ($parts[3] ?? '') === '' && ($parts[4] ?? '') === '') {
            return null;
        }

        $years  = (int) ($parts[1] ?? 0);
        $months = (int) ($parts[2] ?? 0);
        $weeks  = (int) ($parts[3] ?? 0);
        $days   = (int) ($parts[4] ?? 0);

        return ($years * 365) + ($months * 30) + ($weeks * 7) + $days;
    }//end durationToDays()

    /**
     * Raise a ZGW validation error, aborting the write.
     *
     * @param string $name   The field name.
     * @param string $code   The error code.
     * @param string $reason The human-readable reason.
     *
     * @return void
     *
     * @throws CustomValidationException Always.
     */
    private function fail(string $name, string $code, string $reason): void
    {
        throw new CustomValidationException($reason, [['name' => $name, 'code' => $code, 'reason' => $reason]]);
    }//end fail()
}//end class
