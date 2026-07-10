<?php

/**
 * Unit tests for the Portaliq portal contribution provider.
 *
 * Pins zaakafhandelapp's ADR-046 contract-v2.2 contribution: the
 * dependency-free duck-typed shape (inert without portaliq), the v2
 * getAudiences() + v1 getAudience() pair, the `citizen` read manifest (scoping
 * map, the single `bsn` claim, low trust, the one FORWARD via-join to zaak
 * through rol and the two REVERSE via-joins to taak/bericht through klant), and
 * the subject-safe field projections. Also pins every scopeField, `via` join
 * field and projected read field against the app's shipped data model at HEAD
 * (`src/entities/*.ts`, plus the runtime `betrokkeneIdentificatie.inpBsn`
 * dot-path evidenced in RolDetails.vue + RollenController.php) so a schema
 * drift — a renamed scope property, a dropped whitelist field — fails here
 * instead of silently scoping portal reads to nothing or leaking a staff-only
 * column.
 *
 * Subjects use nil-pattern UUIDs per the change design.md Seed Data section —
 * self-evidently fake, never colliding with live data. The provider is
 * constructed directly — it is a plain dependency-free class by contract
 * (amendment A1), so no mocks and no container are involved.
 *
 * @category  Tests
 * @package   OCA\ZaakAfhandelApp\Tests\Unit\Portal
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link      https://conduction.nl
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Portal;

use OCA\ZaakAfhandelApp\Portal\PortalContributionProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Pin the declarative portal contribution manifest.
 *
 * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
 */
#[CoversClass(PortalContributionProvider::class)]
final class PortalContributionProviderTest extends TestCase
{
    /**
     * Server-derived subject fixture for the citizen audience (nil UUIDs).
     *
     * @var array<string, mixed>
     */
    private const CITIZEN_SUBJECT = [
        'subjectRef'   => '00000000-0000-0000-0000-000000000001',
        'audience'     => 'citizen',
        'organisation' => '00000000-0000-0000-0000-000000000002',
        'trust'        => 'low',
    ];

    /**
     * The register used across the manifest.
     *
     * @var string
     */
    private const REGISTER = 'zaakafhandelapp';

    /**
     * The valid property set of each referenced schema, mirrored from the app's
     * shipped data model at HEAD. This is the register-drift anchor: if a
     * scope/via/projection field below is renamed away, the pin test fails.
     *
     * Sources (verify against HEAD when this changes):
     *  - zaak    : src/entities/zaak/zaak.ts public props (+ status/resultaat/
     *              rollen/besluiten from docs/json/zaken_*.json).
     *  - rol     : src/entities/rol/rol.ts public props; the runtime dot-path
     *              'betrokkeneIdentificatie.inpBsn' from
     *              src/views/rollen/RolDetails.vue:60 +
     *              lib/Controller/RollenController.php:118 (issue #279).
     *  - klant   : src/entities/klanten/klanten.ts public props.
     *  - taak    : src/entities/taak/taak.ts public props.
     *  - bericht : src/entities/bericht/bericht.ts public props.
     *
     * @var array<string, array<int, string>>
     */
    private const SCHEMA_PROPERTIES = [
        'zaak' => [
            'id', 'uuid', 'omschrijving', 'identificatie', 'url', 'bronorganisatie',
            'toelichting', 'zaaktype', 'archiefstatus', 'registratiedatum',
            'verantwoordelijkeOrganisatie', 'startdatum', 'einddatum', 'einddatumGepland',
            'uiterlijkeEinddatumAfdoening', 'publicatiedatum', 'communicatiekanaal',
            'betalingsindicatie', 'betalingsindicatieWeergave', 'laatsteBetaaldatum',
            'selectielijstklasse', 'hoofdzaak', 'klant', 'berichten', 'status',
            'resultaat', 'rollen', 'besluiten',
        ],
        'rol' => [
            'id', 'url', 'uuid', 'zaak', 'betrokkene', 'betrokkeneType',
            'afwijkendeNaamBetrokkene', 'roltype', 'omschrijving', 'omschrijvingGeneriek',
            'roltoelichting', 'registratiedatum', 'indicatieMachtiging', 'contactpersoonRol',
            'statussen', 'betrokkeneIdentificatie', '_expand',
            // Runtime polymorphic natuurlijk-persoon sub-property (dot-path).
            'betrokkeneIdentificatie.inpBsn',
        ],
        'klant' => [
            'id', 'type', 'voornaam', 'tweedeVoornaam', 'tussenvoegsel', 'achternaam',
            'bsn', 'geboortedatum', 'geslacht', 'land', 'telefoonnummer', 'emailadres',
            'straatnaam', 'plaats', 'postcode', 'huisnummer', 'functie', 'aanmaakkanaal',
            'bronorganisatie', 'bedrijfsnaam', 'kvkNummer', 'websiteUrl', 'url',
            'geverifieerd', 'subject', 'subjectIdentificatie', 'subjectType',
        ],
        'taak' => [
            'id', 'title', 'zaak', 'type', 'status', 'deadline', 'onderwerp',
            'toelichting', 'actie', 'klant', 'contactmoment', 'medewerker',
        ],
        'bericht' => [
            'id', 'title', 'batchID', 'aanmaakDatum', 'berichtLeverancierID', 'berichtID',
            'berichtType', 'publicatieDatum', 'onderwerp', 'berichttekst', 'referentie',
            'gebruikerID', 'soortGebruiker', 'inhoud', 'bijlageType', 'omschrijving',
            'volgorde',
        ],
    ];

    /**
     * The provider under test (direct construction — no container).
     *
     * @var PortalContributionProvider
     */
    private PortalContributionProvider $provider;

    /**
     * Construct the provider directly before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new PortalContributionProvider();
    }

    /**
     * The provider is a plain, dependency-free, duck-typed contract (A1):
     * constructible with no args, no `implements`, and it exposes exactly the
     * three probed methods.
     *
     * @return void
     */
    public function testInertDuckTypedContract(): void
    {
        $reflection = new ReflectionClass(PortalContributionProvider::class);

        $this->assertSame([], $reflection->getInterfaceNames(), 'Provider must not implement any interface (duck-typed).');

        $constructor = $reflection->getConstructor();
        $this->assertTrue(
            $constructor === null || $constructor->getNumberOfRequiredParameters() === 0,
            'Provider must be constructible with no required arguments (dependency-free).'
        );

        $this->assertTrue(method_exists($this->provider, 'getAudiences'));
        $this->assertTrue(method_exists($this->provider, 'getAudience'));
        $this->assertTrue(method_exists($this->provider, 'getContribution'));
    }

    /**
     * getAudiences() (v2) lists exactly the citizen audience; getAudience()
     * (v1 fallback) returns it as the primary.
     *
     * @return void
     */
    public function testAudienceContract(): void
    {
        $this->assertSame(['citizen'], $this->provider->getAudiences());
        $this->assertSame('citizen', $this->provider->getAudience());
    }

    /**
     * An audience the provider does not serve — and the missing/empty case —
     * fail closed to null (never a partial or foreign manifest).
     *
     * @return void
     */
    public function testForeignAudienceReturnsNull(): void
    {
        $this->assertNull($this->provider->getContribution(['audience' => 'signer']));
        $this->assertNull($this->provider->getContribution(['audience' => '']));
        $this->assertNull($this->provider->getContribution([]));
    }

    /**
     * The citizen manifest has the expected top-level shape: the section label,
     * three read collections, no actions (creates deferred) and no
     * notifications.
     *
     * @return void
     */
    public function testCitizenManifestShape(): void
    {
        $manifest = $this->provider->getContribution(self::CITIZEN_SUBJECT);

        $this->assertIsArray($manifest);
        $this->assertSame('Mijn Zaken', $manifest['label']);
        $this->assertArrayHasKey('collections', $manifest);
        $this->assertCount(3, $manifest['collections']);
        $this->assertSame([], $manifest['actions'], 'Wave 1 is read-only — creates are deferred (design.md).');
        $this->assertSame([], $manifest['notifications']);

        $ids = array_column($manifest['collections'], 'id');
        $this->assertSame(['citizenZaken', 'citizenTaken', 'citizenBerichten'], $ids);
    }

    /**
     * Every collection scopes by the single server-managed `bsn` claim, is
     * listable, and is `minTrust: 'low'` for the password edge.
     *
     * @return void
     */
    public function testEveryCollectionIsBsnScopedLowTrustAndListable(): void
    {
        foreach ($this->collections() as $collection) {
            $this->assertSame('bsn', $collection['scopeClaim'], $collection['id'].' must scope by the bsn claim.');
            $this->assertSame(self::REGISTER, $collection['register']);
            $this->assertTrue($collection['listable']);
            $this->assertSame('low', $collection['minTrust'], $collection['id'].' must be low trust (password edge).');
        }
    }

    /**
     * Berichten is surfaced as an inbox; zaken + taken are not.
     *
     * @return void
     */
    public function testBerichtenIsInbox(): void
    {
        $collections = $this->collectionsById();

        $this->assertSame('inbox', $collections['citizenBerichten']['kind']);
        $this->assertArrayNotHasKey('kind', $collections['citizenZaken']);
        $this->assertArrayNotHasKey('kind', $collections['citizenTaken']);
    }

    /**
     * Every `via` join is structurally valid against portaliq's isValidVia:
     * exactly the four required string members, an optional `match` of `id` or
     * `scopeField`, and no nested `via` (one hop maximum).
     *
     * @return void
     */
    public function testViaJoinsAreStructurallyValid(): void
    {
        foreach ($this->collections() as $collection) {
            $via = ($collection['via'] ?? null);
            $this->assertIsArray($via, $collection['id'].' declares a via join.');

            foreach (['register', 'schema', 'scopeField', 'targetField'] as $member) {
                $this->assertIsString($via[$member] ?? null, $collection['id'].' via.'.$member.' must be a string.');
                $this->assertNotSame('', $via[$member], $collection['id'].' via.'.$member.' must be non-empty.');
            }

            $this->assertArrayNotHasKey('via', $via, 'A via join must not nest another via (one hop maximum).');

            if (array_key_exists('match', $via) === true) {
                $this->assertContains($via['match'], ['id', 'scopeField'], $collection['id'].' via.match must be id or scopeField.');
            }
        }
    }

    /**
     * The zaken join is the canonical FORWARD (rol -> zaak, match: id); the
     * taken + berichten joins are REVERSE (klant.bsn -> the outer klant/gebruikerID
     * reference, match: scopeField).
     *
     * @return void
     */
    public function testJoinDirections(): void
    {
        $collections = $this->collectionsById();

        $zaken = $collections['citizenZaken'];
        $this->assertSame('rol', $zaken['via']['schema']);
        $this->assertSame('betrokkeneIdentificatie.inpBsn', $zaken['via']['scopeField']);
        $this->assertSame('zaak', $zaken['via']['targetField']);
        $this->assertSame('id', $zaken['via']['match']);
        $this->assertSame('', $zaken['scopeField'], 'A forward join ignores the outer scopeField.');

        foreach (['citizenTaken' => 'klant', 'citizenBerichten' => 'gebruikerID'] as $id => $outerScopeField) {
            $collection = $collections[$id];
            $this->assertSame('klant', $collection['via']['schema']);
            $this->assertSame('bsn', $collection['via']['scopeField']);
            $this->assertSame('id', $collection['via']['targetField']);
            $this->assertSame('scopeField', $collection['via']['match']);
            $this->assertSame($outerScopeField, $collection['scopeField'], $id.' reverse join matches its own scope field.');
        }
    }

    /**
     * REGISTER-DRIFT PIN: every scopeField (when non-empty), every via
     * scope/target field, and every projected field names a property that
     * exists on its schema in the shipped data model at HEAD (dot-paths
     * verified whole). Guards against a rename silently scoping reads to
     * nothing or dropping a projected column.
     *
     * @return void
     */
    public function testEveryReferencedFieldExistsOnItsSchema(): void
    {
        foreach ($this->collections() as $collection) {
            $schema = $collection['schema'];

            // Outer scopeField (only meaningful/used for reverse joins).
            if (($collection['scopeField'] ?? '') !== '') {
                $this->assertFieldExists($schema, $collection['scopeField'], $collection['id'].' scopeField');
            }

            // The via join fields, checked against the JOIN schema.
            $via = $collection['via'];
            $this->assertFieldExists($via['schema'], $via['scopeField'], $collection['id'].' via.scopeField');
            $this->assertFieldExists($via['schema'], $via['targetField'], $collection['id'].' via.targetField');

            // Every projected field, checked against the collection's schema.
            foreach ($collection['fields'] as $field) {
                $this->assertFieldExists($schema, $field, $collection['id'].' projected field');
            }
        }
    }

    /**
     * Projections drop the staff-only / other-party / routing-internal columns
     * enumerated in design.md — a positive assertion that the leak surfaces
     * stay closed.
     *
     * @return void
     */
    public function testProjectionsDropSensitiveColumns(): void
    {
        $collections = $this->collectionsById();

        // Zaak: other parties (rollen), org routing, financial + archival internals.
        foreach (['rollen', 'bronorganisatie', 'verantwoordelijkeOrganisatie', 'betalingsindicatie', 'archiefstatus', 'klant', 'url'] as $dropped) {
            $this->assertNotContains($dropped, $collections['citizenZaken']['fields'], 'zaak projection must drop '.$dropped);
        }

        // Taak: the staff handler + internal linkage.
        foreach (['medewerker', 'contactmoment', 'klant'] as $dropped) {
            $this->assertNotContains($dropped, $collections['citizenTaken']['fields'], 'taak projection must drop '.$dropped);
        }

        // Bericht: the routing discriminators + supplier/batch internals.
        foreach (['gebruikerID', 'soortGebruiker', 'berichtLeverancierID', 'batchID', 'berichtID', 'volgorde'] as $dropped) {
            $this->assertNotContains($dropped, $collections['citizenBerichten']['fields'], 'bericht projection must drop '.$dropped);
        }
    }

    /**
     * Assert a field (possibly a dot-path) exists on a schema's property set.
     *
     * @param string $schema  The schema slug.
     * @param string $field   The field or dot-path.
     * @param string $context A human label for the failure message.
     *
     * @return void
     */
    private function assertFieldExists(string $schema, string $field, string $context): void
    {
        $this->assertArrayHasKey($schema, self::SCHEMA_PROPERTIES, 'Unknown schema in fixture: '.$schema);
        $this->assertContains(
            $field,
            self::SCHEMA_PROPERTIES[$schema],
            $context." '".$field."' does not exist on schema '".$schema."' (register drift?)."
        );
    }

    /**
     * The citizen collections.
     *
     * @return array<int, array<string, mixed>>
     */
    private function collections(): array
    {
        $manifest = $this->provider->getContribution(self::CITIZEN_SUBJECT);
        return $manifest['collections'];
    }

    /**
     * The citizen collections keyed by id.
     *
     * @return array<string, array<string, mixed>>
     */
    private function collectionsById(): array
    {
        $byId = [];
        foreach ($this->collections() as $collection) {
            $byId[$collection['id']] = $collection;
        }

        return $byId;
    }
}
