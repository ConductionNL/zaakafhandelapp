<?php

namespace OCA\ZaakAfhandelApp\Controller;

use GuzzleHttp\Client;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

/**
 * Geeft invulling aan https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/
 */
class RollenController extends Controller
{
    const TEST_ARRAY = [
        "095be615-a8ad-4c33-8e9c-c7612fbf6c9f" => [
            "url" => "http://example.com",
            "uuid" => "095be615-a8ad-4c33-8e9c-c7612fbf6c9f",
            "zaak" => "http://example.com/api/zrc/v1/zaken/id",
            "betrokkene" => "http://example.com",
            "betrokkeneType" => "medewerker",
            "afwijkendeNaamBetrokkene" => "string",
            "roltype" => "http://example.com/api/ztc/v1/roltypen/id",
            "omschrijving" => "Rol omschrijving 1",
            "omschrijvingGeneriek" => "string",
            "roltoelichting" => "string",
            "registratiedatum" => "2019-08-24T14:15:22Z",
            "indicatieMachtiging" => "gemachtigde",
            "contactpersoonRol" => [
                "emailadres" => "user@example.com",
                "functie" => "string",
                "telefoonnummer" => "string",
                "naam" => "string"
            ],
            "statussen" => [
                "http://example.com/api/zrc/v1/statussen/id"
            ],
            "_expand" => [
                "zaak" => [],
                "roltype" => [],
                "statussen" => []
            ],
            "betrokkeneIdentificatie" => [
                "identificatie" => "string",
                "achternaam" => "string",
                "voorletters" => "string",
                "voorvoegselAchternaam" => "string"
            ]
        ],
        "d3f5a5c1-e78d-4c33-9e9c-c7612fbf6c9a" => [
            "url" => "http://example.com",
            "uuid" => "d3f5a5c1-e78d-4c33-9e9c-c7612fbf6c9a",
            "zaak" => "http://example.com/api/zrc/v1/zaken/id",
            "betrokkene" => "http://example.com",
            "betrokkeneType" => "medewerker",
            "afwijkendeNaamBetrokkene" => "string",
            "roltype" => "http://example.com/api/ztc/v1/roltypen/id",
            "omschrijving" => "Rol omschrijving 2",
            "omschrijvingGeneriek" => "string",
            "roltoelichting" => "string",
            "registratiedatum" => "2019-08-24T14:15:22Z",
            "indicatieMachtiging" => "gemachtigde",
            "contactpersoonRol" => [
                "emailadres" => "user@example.com",
                "functie" => "string",
                "telefoonnummer" => "string",
                "naam" => "string"
            ],
            "statussen" => [
                "http://example.com/api/zrc/v1/statussen/id"
            ],
            "_expand" => [
                "zaak" => [],
                "roltype" => [],
                "statussen" => []
            ],
            "betrokkeneIdentificatie" => [
                "identificatie" => "string",
                "achternaam" => "string",
                "voorletters" => "string",
                "voorvoegselAchternaam" => "string"
            ]
        ],
        "a5c6b7d8-f9e0-4c33-8e9c-c7612fbf6c9b" => [
            "url" => "http://example.com",
            "uuid" => "a5c6b7d8-f9e0-4c33-8e9c-c7612fbf6c9b",
            "zaak" => "http://example.com/api/zrc/v1/zaken/id",
            "betrokkene" => "http://example.com",
            "betrokkeneType" => "medewerker",
            "afwijkendeNaamBetrokkene" => "string",
            "roltype" => "http://example.com/api/ztc/v1/roltypen/id",
            "omschrijving" => "Rol omschrijving 3",
            "omschrijvingGeneriek" => "string",
            "roltoelichting" => "string",
            "registratiedatum" => "2019-08-24T14:15:22Z",
            "indicatieMachtiging" => "gemachtigde",
            "contactpersoonRol" => [
                "emailadres" => "user@example.com",
                "functie" => "string",
                "telefoonnummer" => "string",
                "naam" => "string"
            ],
            "statussen" => [
                "http://example.com/api/zrc/v1/statussen/id"
            ],
            "betrokkeneIdentificatie" => [
                "identificatie" => "string",
                "achternaam" => "string",
                "voorletters" => "string",
                "voorvoegselAchternaam" => "string"
            ]
        ],
        "b8c7d6e5-f0e1-4c33-8e9c-c7612fbf6c9c" => [
            "url" => "http://example.com",
            "uuid" => "b8c7d6e5-f0e1-4c33-8e9c-c7612fbf6c9c",
            "zaak" => "http://example.com/api/zrc/v1/zaken/id",
            "betrokkene" => "http://example.com",
            "betrokkeneType" => "medewerker",
            "afwijkendeNaamBetrokkene" => "string",
            "roltype" => "http://example.com/api/ztc/v1/roltypen/id",
            "omschrijving" => "Rol omschrijving 4",
            "omschrijvingGeneriek" => "string",
            "roltoelichting" => "string",
            "registratiedatum" => "2019-08-24T14:15:22Z",
            "indicatieMachtiging" => "gemachtigde",
            "contactpersoonRol" => [
                "emailadres" => "user@example.com",
                "functie" => "string",
                "telefoonnummer" => "string",
                "naam" => "string"
            ],
            "statussen" => [
                "http://example.com/api/zrc/v1/statussen/id"
            ],
            "betrokkeneIdentificatie" => [
                "identificatie" => "string",
                "achternaam" => "string",
                "voorletters" => "string",
                "voorvoegselAchternaam" => "string"
            ]
        ]
    ];

    public function __construct(
		$appName,
		IRequest $request,
		private readonly IAppConfig $config
	)
    {
        parent::__construct($appName, $request);
    }

	/**
	 * This returns the template of the main app's page
	 * It adds some data to the template (app version)
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function page(): TemplateResponse
	{			
        return new TemplateResponse(
            //Application::APP_ID,
            'zaakafhandelapp',
            'index',
            []
        );
	}
	

    /**
     * Return (and serach) all objects
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function index(): JSONResponse
    {
        $results = ["results" => self::TEST_ARRAY];
        return new JSONResponse($results);
    }

    /**
     * Read a single object
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function show(string $id): JSONResponse
    {
        $result = self::TEST_ARRAY[$id];
        return new JSONResponse($result);
    }


    /**
     * Creatue an object
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function create(): JSONResponse
    {
        // get post from requests
        return new JSONResponse([]);
    }

    /**
     * Update an object
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function update(string $id): JSONResponse
    {
        $result = self::TEST_ARRAY[$id];
        return new JSONResponse($result);
    }

    /**
     * Delate an object
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function destroy(string $id): JSONResponse
    {
        return new JSONResponse([]);
    }
}
