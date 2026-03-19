<?php

namespace OCA\ZaakAfhandelApp\Service;

/**
 * Service for ZGW registry and schema slug lookups.
 *
 * Provides a centralized place for register and schema identifiers
 * used across ZGW services.
 */
class ZGWRegistryService
{

    /**
     * Register slug mappings.
     */
    private const REGISTERS = [
        'drc' => 'documenten',
        'brc' => 'besluiten',
        'zrc' => 'zaken',
        'ztc' => 'catalogi',
    ];

    /**
     * Schema slug mappings.
     */
    private const SCHEMAS = [
        'zio'             => 'zaakinformatieobject',
        'bio'             => 'besluitinformatieobject',
        'oio'             => 'objectinformatieobject',
        'besluit'         => 'besluit',
        'zaak'            => 'zaak',
        'status'          => 'status',
        'gebruiksrechten' => 'gebruiksrechten',
        'zaakbesluit'     => 'zaakbesluit',
    ];

    public function getDrcRegister(): string
    {
        return self::REGISTERS['drc'];
    }

    public function getBrcRegister(): string
    {
        return self::REGISTERS['brc'];
    }

    public function getZrcRegister(): string
    {
        return self::REGISTERS['zrc'];
    }

    public function getZtcRegister(): string
    {
        return self::REGISTERS['ztc'];
    }

    public function getGebruiksrechtenSchema(): string
    {
        return self::SCHEMAS['gebruiksrechten'];
    }

    public function getZioSchema(): string
    {
        return self::SCHEMAS['zio'];
    }

    public function getBioSchema(): string
    {
        return self::SCHEMAS['bio'];
    }

    public function getOioSchema(): string
    {
        return self::SCHEMAS['oio'];
    }

    public function getBesluitSchema(): string
    {
        return self::SCHEMAS['besluit'];
    }

    public function getZaakSchema(): string
    {
        return self::SCHEMAS['zaak'];
    }

    public function getStatusSchema(): string
    {
        return self::SCHEMAS['status'];
    }

    public function getZaakBesluitSchema(): string
    {
        return self::SCHEMAS['zaakbesluit'];
    }

    /**
     * Extract an object ID from an endpoint URL.
     *
     * @param string $url The endpoint URL
     *
     * @return string The extracted object ID
     */
    public function getObjectIdByEndpointUrl(string $url): string
    {
        $explodedUrl = explode('/', $url);
        return end($explodedUrl);
    }
}
