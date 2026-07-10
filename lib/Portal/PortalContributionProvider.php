<?php

/**
 * Zaakafhandelapp Portal Contribution Provider
 *
 * Zaakafhandelapp's contribution to the shared Portaliq external portal (hydra
 * ADR-046 + contract v2.2). Portaliq — the ONE shared portal for people
 * WITHOUT Nextcloud accounts — discovers this class by convention FQCN
 * (`OCA\{Namespace}\Portal\PortalContributionProvider`) and duck-types it via
 * method_exists(), never instanceof. This class is therefore deliberately
 * PLAIN: no portaliq imports, no `implements` clause, no info.xml dependency,
 * no constructor dependencies. Without portaliq installed it is inert and
 * zaakafhandelapp behaves exactly as before.
 *
 * It declares — for the `citizen` audience — the "Mijn Zaken" read surfaces a
 * data subject may see: their cases (zaken), their tasks (taken) and their
 * message inbox (berichten). DigiD/eHerkenning is DEFERRED: citizens sign in
 * through portaliq's ordinary password / `portalAccount` edge at trust level
 * `low`, exactly like pipelinq's `client`/`customer` audiences. Scoping is by
 * the citizen's ZGW-native identifier — their BSN — carried as a server-managed
 * `claims.zaakafhandelapp.bsn` claim on the portalAccount (never client input),
 * joined one hop to the domain: through `rol` for zaken (a rol identifies the
 * betrokkene by BSN) and through `klant` for taken + berichten (a klant carries
 * the BSN, and taak/bericht reference the klant). See
 * openspec/changes/portal-contribution/design.md for the scope-key decision
 * (BSN vs UUID), the privacy note, and the field-whitelist tables.
 *
 * Wave 1 declares READ collections only — no create-actions. The portaliq
 * writer stamps a create's scope field with the raw subjectRef pseudonym and
 * does NOT resolve `scopeClaim`; because every collection here scopes by the
 * `bsn` claim (or a via-join), no create can be safely server-stamped yet, and
 * any create referencing a specific zaak/klant would be a client-supplied
 * cross-reference the writer cannot verify (write-IDOR, portaliq#16). Creates
 * are deferred with a documented reason (design.md "Deferred creates").
 *
 * Tracking issue: Conduction/zaakafhandelapp#37.
 *
 * @category Portal
 * @package  OCA\ZaakAfhandelApp\Portal
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @version GIT: <git-id>
 *
 * @link https://conduction.nl
 *
 * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Portal;

/**
 * Declares what an external portal subject may see in zaakafhandelapp.
 *
 * The contribution is a declarative manifest (pure data — no I/O, no
 * callbacks). All subject identity (subjectRef, audience, organisation, trust)
 * is derived server-side by portaliq's auth edge and MUST never be trusted from
 * the client (ADR-005). Scoping uses the citizen's BSN — resolved server-side
 * from their portalAccount `bsn` claim — never a Nextcloud user id, because
 * externals have no Nextcloud account by premise (amendment A4). BSN is a
 * legitimate ZGW domain identifier (the natuurlijk-persoon citizen key), not a
 * Nextcloud uid; it is sensitive PII, so every collection is read-only,
 * field-projected to citizen-safe columns, and design.md records the intent to
 * raise case data to `substantial` trust once the DigiD broker lands.
 *
 * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
 */
class PortalContributionProvider
{
    /**
     * The OpenRegister register slug every collection below lives in.
     *
     * Matches `src/manifest.json` `config.register` for the schema-backed
     * zaak-domain pages.
     *
     * @var string
     */
    private const REGISTER = 'zaakafhandelapp';

    /**
     * The human label portaliq renders for this app's portal section.
     *
     * @var string
     */
    private const LABEL = 'Mijn Zaken';

    /**
     * The audiences this provider contributes to (contract v2, preferred).
     *
     * The registry probes for this method first. Zaakafhandelapp serves the
     * ZGW data subject — the citizen (`citizen`). The `organisation` audience
     * is deferred until a non-natuurlijk-persoon identifier claim path
     * (KvK/RSIN) is modelled: `rol.betrokkeneIdentificatie` only cleanly
     * exposes the natuurlijk-persoon BSN today (design.md "Audiences").
     *
     * @return array<int, string> The audience identifiers.
     *
     * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
     */
    public function getAudiences(): array
    {
        return ['citizen'];

    }//end getAudiences()

    /**
     * The primary audience this provider contributes to (contract v1 fallback).
     *
     * Kept alongside getAudiences() so the provider also works against a v1
     * registry that predates multi-audience support.
     *
     * @return string The primary audience identifier.
     *
     * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
     */
    public function getAudience(): string
    {
        return 'citizen';

    }//end getAudience()

    /**
     * Build the declarative portal manifest for one resolved subject.
     *
     * The subject array is server-derived by portaliq (subjectRef UUID,
     * audience, organisation, trust level low|substantial|high). Returns null
     * for any audience zaakafhandelapp does not serve (fail-closed; the
     * registry already filters by audience, but a provider must not rely on
     * that). This wave declares read collections only — no create or endpoint
     * actions.
     *
     * @param array<string, mixed> $subject The resolved portal subject.
     *
     * @return array<string, mixed>|null The manifest, or null when not contributing.
     *
     * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
     */
    public function getContribution(array $subject): ?array
    {
        $audience = ($subject['audience'] ?? '');

        if ($audience === 'citizen') {
            return $this->citizenContribution();
        }

        return null;

    }//end getContribution()

    /**
     * Manifest for the `citizen` audience (a ZGW natuurlijk-persoon data subject).
     *
     * Read surfaces are BSN-scoped through a single server-managed claim
     * (`claims.zaakafhandelapp.bsn`) joined one hop to the domain:
     *
     * - `citizenZaken` — a FORWARD via-join (`match: 'id'`) over `rol`: the
     *   join collects the `zaak` reference of every rol whose
     *   `betrokkeneIdentificatie.inpBsn` equals the citizen's BSN, then keeps
     *   the zaken whose own id is in that set. A citizen is a party to a case
     *   precisely when a rol identifies them, which is the canonical ZGW
     *   ownership model (design.md); staff roles carry no `inpBsn` and so never
     *   match. Projected to citizen-safe case columns — every internal
     *   organisation, financial, archival and other-party (`rollen`) field is
     *   dropped.
     * - `citizenTaken` — a REVERSE via-join (`match: 'scopeField'`) over
     *   `klant`: the join collects the `id` of every klant whose `bsn` equals
     *   the citizen's BSN, then keeps the taken whose own `klant` reference is
     *   in that set. Projected to the task's own facts; the staff `medewerker`
     *   handler and the internal `contactmoment`/`klant` linkage are dropped.
     * - `citizenBerichten` — the same reverse via-join over `klant` applied to
     *   `bericht.gebruikerID` (which holds the klant id), surfaced as an
     *   `inbox`. Projected to the message body; the `gebruikerID` /
     *   `soortGebruiker` routing keys and supplier/batch internals are dropped.
     *
     * Everything is `minTrust: 'low'` for the password edge; design.md records
     * that case data SHOULD be raised to `substantial` once DigiD lands.
     *
     * @return array<string, mixed> The citizen manifest.
     *
     * @spec openspec/changes/portal-contribution/specs/portal-contribution/spec.md
     */
    private function citizenContribution(): array
    {
        return [
            'label'         => self::LABEL,
            'collections'   => [
                [
                    'id'         => 'citizenZaken',
                    'register'   => self::REGISTER,
                    'schema'     => 'zaak',
                    'scopeField' => '',
                    'scopeClaim' => 'bsn',
                    'via'        => [
                        'register'    => self::REGISTER,
                        'schema'      => 'rol',
                        'scopeField'  => 'betrokkeneIdentificatie.inpBsn',
                        'targetField' => 'zaak',
                        'match'       => 'id',
                    ],
                    'label'      => 'My cases',
                    'listable'   => true,
                    'minTrust'   => 'low',
                    'fields'     => [
                        'identificatie',
                        'omschrijving',
                        'toelichting',
                        'zaaktype',
                        'registratiedatum',
                        'startdatum',
                        'einddatum',
                        'einddatumGepland',
                        'uiterlijkeEinddatumAfdoening',
                        'publicatiedatum',
                        'communicatiekanaal',
                        'status',
                        'resultaat',
                    ],
                ],
                [
                    'id'         => 'citizenTaken',
                    'register'   => self::REGISTER,
                    'schema'     => 'taak',
                    'scopeField' => 'klant',
                    'scopeClaim' => 'bsn',
                    'via'        => [
                        'register'    => self::REGISTER,
                        'schema'      => 'klant',
                        'scopeField'  => 'bsn',
                        'targetField' => 'id',
                        'match'       => 'scopeField',
                    ],
                    'label'      => 'My tasks',
                    'listable'   => true,
                    'minTrust'   => 'low',
                    'fields'     => [
                        'title',
                        'onderwerp',
                        'toelichting',
                        'type',
                        'status',
                        'deadline',
                        'actie',
                        'zaak',
                    ],
                ],
                [
                    'id'         => 'citizenBerichten',
                    'register'   => self::REGISTER,
                    'schema'     => 'bericht',
                    'scopeField' => 'gebruikerID',
                    'scopeClaim' => 'bsn',
                    'via'        => [
                        'register'    => self::REGISTER,
                        'schema'      => 'klant',
                        'scopeField'  => 'bsn',
                        'targetField' => 'id',
                        'match'       => 'scopeField',
                    ],
                    'kind'       => 'inbox',
                    'label'      => 'My messages',
                    'listable'   => true,
                    'minTrust'   => 'low',
                    'fields'     => [
                        'onderwerp',
                        'berichttekst',
                        'inhoud',
                        'berichtType',
                        'bijlageType',
                        'omschrijving',
                        'referentie',
                        'aanmaakDatum',
                        'publicatieDatum',
                    ],
                ],
            ],
            'actions'       => [],
            'notifications' => [],
        ];

    }//end citizenContribution()
}//end class
