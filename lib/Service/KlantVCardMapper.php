<?php

namespace OCA\ZaakAfhandelApp\Service;

/**
 * Pure shape mapping between Nextcloud vCard properties and klant fields.
 *
 * Split out of KlantContactSyncService: that class owns the side effects
 * (talking to the Contacts manager and to OpenRegister, logging, deciding what
 * to sync), while this one owns only the field translation and has no
 * collaborators at all. Keeping the two apart makes the mapping directly
 * testable and stops the sync service from carrying the mapping's branching.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class KlantVCardMapper
{
    /**
     * Klant fields that have a privacy-sensitive nature and SHALL never be
     * written to a shared addressbook vCard (REQ-003).
     *
     * @var array<int, string>
     */
    private const NON_VCARD_FIELDS = ['bsn'];

    /**
     * Map a Nextcloud vCard result to klant fields (REQ-002).
     *
     * @param array<string, mixed> $contact The vCard key-value result.
     * @param ?string              $type    Optional explicit klant type.
     *
     * @return array<string, mixed> The klant field set.
     *
     * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-002
     */
    public function vCardToKlant(array $contact, ?string $type=null): array
    {
        $org = $this->firstValue(($contact['ORG'] ?? ''));

        // An explicit type always wins; otherwise a company name means the
        // contact describes an organisation rather than a person.
        $type = ($type ?? (($org !== '') ? 'organisatie' : 'persoon'));

        $klant = [
            'type'           => $type,
            'emailadres'     => $this->firstValue(($contact['EMAIL'] ?? '')),
            'telefoonnummer' => $this->firstValue(($contact['TEL'] ?? '')),
            'bedrijfsnaam'   => $org,
        ];

        $klant = array_merge($klant, $this->nameFieldsFromVCard($contact));
        $klant = array_merge($klant, $this->addressFieldsFromVCard($contact));

        // Every $klant value is produced by firstValue()/trim(), both of which
        // return string, so dropping the empty ones is the whole filter.
        return array_filter($klant, static fn (string $value): bool => $value !== '');
    }//end vCardToKlant()

    /**
     * Derive the klant name fields from a vCard.
     *
     * The structured N property wins; FN is only split when N carried nothing.
     *
     * @param array<string, mixed> $contact The vCard key-value result.
     *
     * @return array<string, string> The name fields, possibly empty.
     */
    private function nameFieldsFromVCard(array $contact): array
    {
        // N = Family;Given;Additional;Prefix;Suffix
        $structuredName = $this->firstValue(($contact['N'] ?? ''));
        if ($structuredName !== '') {
            $parts = explode(';', $structuredName);

            return [
                'achternaam'    => trim($parts[0]),
                'voornaam'      => trim(($parts[1] ?? '')),
                'tussenvoegsel' => trim(($parts[3] ?? '')),
            ];
        }

        $formattedName = $this->firstValue(($contact['FN'] ?? ''));
        if ($formattedName === '') {
            return [];
        }

        $bits = preg_split('/\s+/', trim($formattedName), 2);

        return [
            'voornaam'   => ($bits[0] ?? ''),
            'achternaam' => ($bits[1] ?? ''),
        ];
    }//end nameFieldsFromVCard()

    /**
     * Derive the klant address fields from a vCard.
     *
     * @param array<string, mixed> $contact The vCard key-value result.
     *
     * @return array<string, string> The address fields, possibly empty.
     */
    private function addressFieldsFromVCard(array $contact): array
    {
        // ADR = PObox;Extended;Street;City;Region;PostalCode;Country
        $address = $this->firstValue(($contact['ADR'] ?? ''));
        if ($address === '') {
            return [];
        }

        $adr = explode(';', $address);

        return [
            'straatnaam' => trim(($adr[2] ?? '')),
            'plaats'     => trim(($adr[3] ?? '')),
            'postcode'   => trim(($adr[5] ?? '')),
            'land'       => trim(($adr[6] ?? '')),
        ];
    }//end addressFieldsFromVCard()

    /**
     * Map a klant to vCard properties (REQ-003).
     *
     * Privacy-sensitive fields (bsn) are never emitted. When $uid is provided
     * the property set carries it so an update replaces the existing card.
     *
     * @param array<string, mixed> $klant The klant.
     * @param ?string              $uid   The existing contact uid, or null for a new card.
     *
     * @return array<string, mixed> The vCard property key-value set.
     *
     * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-003
     */
    public function klantToVCard(array $klant, ?string $uid): array
    {
        $properties = [];

        if (($uid ?? '') !== '') {
            $properties['UID'] = $uid;
        }

        $properties = array_merge($properties, $this->nameProperties($klant));

        $email = (string) ($klant['emailadres'] ?? '');
        if ($email !== '') {
            $properties['EMAIL'] = $email;
        }

        $phone = (string) ($klant['telefoonnummer'] ?? '');
        if ($phone !== '') {
            $properties['TEL'] = $phone;
        }

        $properties = array_merge($properties, $this->addressProperties($klant));

        // Defence in depth (REQ-003): the property set is assembled above from
        // an explicit vCard-key allowlist, so a privacy-sensitive klant field
        // can only ever reach here if a future edit copies klant keys through.
        // array_diff_key drops them unconditionally, without depending on the
        // statically inferred array shape the way an unset() loop does.
        return array_diff_key($properties, array_flip(self::NON_VCARD_FIELDS));
    }//end klantToVCard()

    /**
     * Build the name-carrying vCard properties for a klant.
     *
     * @param array<string, mixed> $klant The klant.
     *
     * @return array<string, string> The FN/N/ORG properties.
     */
    private function nameProperties(array $klant): array
    {
        $type         = ($klant['type'] ?? 'persoon');
        $bedrijfsnaam = (string) ($klant['bedrijfsnaam'] ?? '');

        // An organisation card is just the company name.
        if ($type === 'organisatie' && $bedrijfsnaam !== '') {
            return [
                'FN'  => $bedrijfsnaam,
                'ORG' => $bedrijfsnaam,
            ];
        }

        $voornaam      = (string) ($klant['voornaam'] ?? '');
        $tussenvoegsel = (string) ($klant['tussenvoegsel'] ?? '');
        $achternaam    = (string) ($klant['achternaam'] ?? '');
        $family        = trim(trim($tussenvoegsel.' '.$achternaam));

        $properties = [
            'FN' => trim($voornaam.' '.$family),
            // N = Family;Given;Additional;Prefix;Suffix
            'N'  => $family.';'.$voornaam.';;'.$tussenvoegsel.';',
        ];

        // A person may still carry the company they work for.
        if ($bedrijfsnaam !== '') {
            $properties['ORG'] = $bedrijfsnaam;
        }

        return $properties;
    }//end nameProperties()

    /**
     * Build the ADR vCard property for a klant, when it has any address at all.
     *
     * @param array<string, mixed> $klant The klant.
     *
     * @return array<string, string> The ADR property, or an empty array.
     */
    private function addressProperties(array $klant): array
    {
        $street  = trim((string) ($klant['straatnaam'] ?? '').' '.(string) ($klant['huisnummer'] ?? ''));
        $city    = (string) ($klant['plaats'] ?? '');
        $postal  = (string) ($klant['postcode'] ?? '');
        $country = (string) ($klant['land'] ?? '');

        // One concatenation rather than four separate emptiness tests: ADR is
        // emitted as soon as any single component carries a value.
        if ($street.$city.$postal.$country === '') {
            return [];
        }

        // ADR = PObox;Extended;Street;City;Region;PostalCode;Country
        return ['ADR' => ';;'.$street.';'.$city.';;'.$postal.';'.$country];
    }//end addressProperties()

    /**
     * Extract the first scalar value from a vCard property that may be an
     * array (multi-valued / typed) or a string.
     *
     * @param mixed $value The raw property value.
     *
     * @return string The first scalar value as a string.
     */
    public function firstValue(mixed $value): string
    {
        if (is_array($value) === false) {
            return (string) $value;
        }

        $first = ($value[0] ?? '');
        if (is_array($first) === true) {
            return (string) ($first['value'] ?? '');
        }

        return (string) $first;
    }//end firstValue()
}//end class
