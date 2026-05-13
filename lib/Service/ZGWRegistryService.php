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
        'zio'                           => 'zaakinformatieobject',
        'bio'                           => 'besluitinformatieobject',
        'oio'                           => 'objectinformatieobject',
        'besluit'                       => 'besluit',
        'zaak'                          => 'zaak',
        'status'                        => 'status',
        'gebruiksrechten'               => 'gebruiksrechten',
        'zaakbesluit'                   => 'zaakbesluit',
        'informatieobjecttype'          => 'informatieobjecttype',
        'zaaktype-informatieobjecttype' => 'zaaktypeinformatieobjecttype',
        'zaaktype'                      => 'zaaktype',
    ];

    public function getDrcRegister(): string
    {
        return self::REGISTERS['drc'];
    }//end getDrcRegister()

    public function getBrcRegister(): string
    {
        return self::REGISTERS['brc'];
    }//end getBrcRegister()

    public function getZrcRegister(): string
    {
        return self::REGISTERS['zrc'];
    }//end getZrcRegister()

    public function getZtcRegister(): string
    {
        return self::REGISTERS['ztc'];
    }//end getZtcRegister()

    public function getGebruiksrechtenSchema(): string
    {
        return self::SCHEMAS['gebruiksrechten'];
    }//end getGebruiksrechtenSchema()

    public function getZioSchema(): string
    {
        return self::SCHEMAS['zio'];
    }//end getZioSchema()

    public function getBioSchema(): string
    {
        return self::SCHEMAS['bio'];
    }//end getBioSchema()

    public function getOioSchema(): string
    {
        return self::SCHEMAS['oio'];
    }//end getOioSchema()

    public function getBesluitSchema(): string
    {
        return self::SCHEMAS['besluit'];
    }//end getBesluitSchema()

    public function getZaakSchema(): string
    {
        return self::SCHEMAS['zaak'];
    }//end getZaakSchema()

    public function getStatusSchema(): string
    {
        return self::SCHEMAS['status'];
    }//end getStatusSchema()

    public function getZaakBesluitSchema(): string
    {
        return self::SCHEMAS['zaakbesluit'];
    }//end getZaakBesluitSchema()

    public function getIOTSchema(): string
    {
        return self::SCHEMAS['informatieobjecttype'];
    }//end getIOTSchema()

    public function getZTIOTSchema(): string
    {
        return self::SCHEMAS['zaaktype-informatieobjecttype'];
    }//end getZTIOTSchema()

    public function getZaakTypeSchema(): string
    {
        return self::SCHEMAS['zaaktype'];
    }//end getZaakTypeSchema()

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
    }//end getObjectIdByEndpointUrl()
}//end class
