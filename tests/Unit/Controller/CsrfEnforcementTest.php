<?php

/**
 * Locks CSRF enforcement on the ten mutating endpoints whose exemption was removed.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Controller
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Controller;

use OCA\ZaakAfhandelApp\Controller\BerichtenController;
use OCA\ZaakAfhandelApp\Controller\ContactMomentenController;
use OCA\ZaakAfhandelApp\Controller\KlantContactsController;
use OCA\ZaakAfhandelApp\Controller\KlantenController;
use OCA\ZaakAfhandelApp\Controller\MedewerkersController;
use OCA\ZaakAfhandelApp\Controller\ObjectsController;
use OCA\ZaakAfhandelApp\Controller\SettingsController;
use OCA\ZaakAfhandelApp\Controller\TakenController;
use OCA\ZaakAfhandelApp\Controller\ZaakTypenController;
use OCA\ZaakAfhandelApp\Controller\ZakenController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * WHY THIS TEST EXISTS
 * --------------------
 * Every mutating endpoint in this app used to carry `@NoCSRFRequired`, so the
 * server was not checking a token. The frontend was fixed first (#373 — all 27
 * mutating call sites now send a real `requesttoken` via `@nextcloud/auth`),
 * and the exemption was then dropped from the ten controller methods those call
 * sites reach.
 *
 * The regression this guards against is silent and asymmetric: putting the tag
 * back is a ONE-LINE edit in a docblock, it changes no behaviour any other test
 * observes, and the endpoint simply stops checking the token again. Nothing
 * fails. A request that should be rejected is accepted, which is the failure
 * mode that looks exactly like a pass.
 *
 * ⚠️ THE TAG DOES NOT HAVE TO BE MEANT TO COUNT
 * ---------------------------------------------
 * On larpingapp#298 the *sentence announcing the removal* —
 * `* @NoCSRFRequired removed to close the CSRF-forgery surface` — sat at
 * docblock-tag position, where Nextcloud's `ControllerMethodReflector` reads it
 * as the annotation being PRESENT. The prose describing the fix WAS the hole.
 * That is why this test asks the same question Nextcloud asks, of the docblock,
 * rather than trusting a code review to spot the difference between a tag and a
 * comment about a tag.
 *
 * HOW THE QUESTION IS ASKED
 * -------------------------
 * `SecurityMiddleware::hasAnnotationOrAttribute()` accepts EITHER form:
 *
 *   1. the `#[NoCSRFRequired]` attribute, via `ReflectionMethod::getAttributes()`;
 *   2. the `@NoCSRFRequired` docblock tag, via `ControllerMethodReflector`,
 *      which extracts tags with
 *      `/^\h+\*\h+@(?P<annotation>[A-Z]\w+)((?P<parameter>.*))?$/m`.
 *
 * `ControllerMethodReflector` is `OC\…` — Nextcloud server-private, and these
 * are pure unit tests with no server runtime (see tests/bootstrap.php), so it
 * cannot be called here. Its tag regex is therefore reproduced verbatim below.
 *
 * A reproduced instrument is only worth as much as its agreement with the
 * original, so that agreement was MEASURED rather than assumed: both were run
 * over all 66 public methods of these ten controllers, in both the pre-change
 * and post-change trees, inside a live Nextcloud 34 container. They agreed on
 * 66/66 in both arms (66 exempt before, 56 exempt + 10 enforced after).
 *
 * The test fails in BOTH directions on purpose: `testTargetsEnforceCsrf` fails
 * if an exemption comes back, and `testDetectorSeesARemainingExemption` fails
 * if the detector has gone blind and would report "enforced" for everything.
 */
class CsrfEnforcementTest extends TestCase
{
    /**
     * Nextcloud's own docblock-tag pattern, copied from
     * `OC\AppFramework\Utility\ControllerMethodReflector::reflect()`.
     *
     * @var string
     */
    private const NC_ANNOTATION_RE = '/^\h+\*\h+@(?P<annotation>[A-Z]\w+)((?P<parameter>.*))?$/m';

    /**
     * Is this method exempt from the CSRF check, by either form Nextcloud honours?
     *
     * @param string $class  Fully-qualified controller class name.
     * @param string $method Method name.
     *
     * @return bool True when Nextcloud would skip the CSRF check.
     */
    private function isCsrfExempt(string $class, string $method): bool
    {
        $reflection = new ReflectionMethod($class, $method);

        // Form 1 — the attribute.
        if (empty($reflection->getAttributes(\OCP\AppFramework\Http\Attribute\NoCSRFRequired::class)) === false) {
            return true;
        }

        // Form 2 — the docblock tag, read exactly as Nextcloud reads it.
        $docs = $reflection->getDocComment();
        if ($docs === false) {
            return false;
        }

        preg_match_all(self::NC_ANNOTATION_RE, $docs, $matches);
        foreach ($matches['annotation'] as $annotation) {
            if (strtolower($annotation) === 'nocsrfrequired') {
                return true;
            }
        }

        return false;
    }

    /**
     * The ten endpoints whose callers were fixed in #373 and which must now
     * enforce CSRF. Each is reached by a mutating `fetch()` under `src/` that
     * sends `requesttoken` from `@nextcloud/auth`.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function enforcedEndpointProvider(): array
    {
        return [
            'POST /settings' => [SettingsController::class, 'create'],
            'POST /api/klanten/contacts/import' => [KlantContactsController::class, 'importContact'],
            'DELETE /api/berichten/{id}' => [BerichtenController::class, 'destroy'],
            'DELETE /api/contactmomenten/{id}' => [ContactMomentenController::class, 'destroy'],
            'DELETE /api/klanten/{id}' => [KlantenController::class, 'destroy'],
            'DELETE /api/medewerkers/{id}' => [MedewerkersController::class, 'destroy'],
            'DELETE /api/taken/{id}' => [TakenController::class, 'destroy'],
            'DELETE /api/ztc/zaaktypen/{id}' => [ZaakTypenController::class, 'destroy'],
            'DELETE /api/zrc/zaken/{id}' => [ZakenController::class, 'destroy'],
            'DELETE /api/objects/{objectType}/{id}' => [ObjectsController::class, 'destroy'],
        ];
    }

    /**
     * No mutating endpoint whose callers send a token may waive the CSRF check.
     *
     * @param string $class  Controller class under test.
     * @param string $method Controller method under test.
     *
     * @return void
     */
    #[DataProvider('enforcedEndpointProvider')]
    public function testTargetsEnforceCsrf(string $class, string $method): void
    {
        $this->assertFalse(
            $this->isCsrfExempt($class, $method),
            sprintf(
                '%s::%s() waives the CSRF check. Its frontend callers send a real requesttoken '
                . '(#373), so the waiver only serves a forged cross-site request. If a docblock '
                . 'sentence mentioning the tag was added, note that Nextcloud reads a tag at '
                . 'docblock-tag position as the annotation itself — see larpingapp#298.',
                $class,
                $method
            )
        );
    }

    /**
     * Positive control — the detector must still be able to answer YES.
     *
     * `ObjectsController::create()` is a genuine remaining exemption in this
     * repo: its callers were fixed by #373 but the write path was out of scope
     * for the removal, which was limited to endpoints with a verified caller
     * inventory. It therefore doubles as proof that a green
     * `testTargetsEnforceCsrf` means "no exemption found" and not "the detector
     * cannot see exemptions at all".
     *
     * If a later change legitimately removes that exemption too, replace the
     * subject here with another still-exempt method rather than deleting this
     * test — a one-sided detector is how a gate goes quietly dead.
     *
     * @return void
     */
    public function testDetectorSeesARemainingExemption(): void
    {
        $this->assertTrue(
            $this->isCsrfExempt(ObjectsController::class, 'create'),
            'The CSRF-exemption detector reported no exemption on a method that has one. '
            . 'Every other assertion in this file is therefore untrustworthy.'
        );
    }
}
