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
class ZakenController extends Controller
{
    const TEST_ARRAY = [
        "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f" => [
            "url"=> "http://example.com",
            "uuid"=> "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
            "identificatie"=> "string",
            "bronorganisatie"=> "string",
            "omschrijving"=> "Zaak 1",
            "toelichting"=> "string",
            "zaaktype"=> "http://example.com",
            "registratiedatum"=> "2019-08-24",
            "verantwoordelijkeOrganisatie"=> "string",
            "startdatum"=> "2019-08-24",
            "einddatum"=> "2019-08-24",
            "einddatumGepland"=> "2019-08-24",
            "uiterlijkeEinddatumAfdoening"=> "2019-08-24",
            "publicatiedatum"=> "2019-08-24",
            "communicatiekanaal"=> "http://example.com",
            "betalingsindicatie"=>"nvt",
            "betalingsindicatieWeergave"=> "string",
            "laatsteBetaaldatum"=> "2019-08-24T14:15:22Z",
            "selectielijstklasse"=> "http://example.com",
            "hoofdzaak"=> "http://example.com",

        ],
        "4c3edd34-a90d-4d2a-8894-adb5836ecde8" => [
            "url"=> "http://example.com",
            "uuid"=> "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
            "identificatie"=> "string",
            "bronorganisatie"=> "string",
            "omschrijving"=> "Zaak 2",
            "toelichting"=> "string",
            "zaaktype"=> "http://example.com",
            "registratiedatum"=> "2019-08-24",
            "verantwoordelijkeOrganisatie"=> "string",
            "startdatum"=> "2019-08-24",
            "einddatum"=> "2019-08-24",
            "einddatumGepland"=> "2019-08-24",
            "uiterlijkeEinddatumAfdoening"=> "2019-08-24",
            "publicatiedatum"=> "2019-08-24",
            "communicatiekanaal"=> "http://example.com",
            "betalingsindicatie"=>"nvt",
            "betalingsindicatieWeergave"=> "string",
            "laatsteBetaaldatum"=> "2019-08-24T14:15:22Z",
            "selectielijstklasse"=> "http://example.com",
            "hoofdzaak"=> "http://example.com",
        ],
        "15551d6f-44e3-43f3-a9d2-59e583c91eb0" => [
            "url"=> "http://example.com",
            "uuid"=> "15551d6f-44e3-43f3-a9d2-59e583c91eb0",
            "identificatie"=> "string",
            "bronorganisatie"=> "string",
            "omschrijving"=> "Zaak 3",
            "toelichting"=> "string",
            "zaaktype"=> "http://example.com",
            "registratiedatum"=> "2019-08-24",
            "verantwoordelijkeOrganisatie"=> "string",
            "startdatum"=> "2019-08-24",
            "einddatum"=> "2019-08-24",
            "einddatumGepland"=> "2019-08-24",
            "uiterlijkeEinddatumAfdoening"=> "2019-08-24",
            "publicatiedatum"=> "2019-08-24",
            "communicatiekanaal"=> "http://example.com",
            "betalingsindicatie"=>"nvt",
            "betalingsindicatieWeergave"=> "string",
            "laatsteBetaaldatum"=> "2019-08-24T14:15:22Z",
            "selectielijstklasse"=> "http://example.com",
            "hoofdzaak"=> "http://example.com",
        ],
        "0a3a0ffb-dc03-4aae-b207-0ed1502e60da" => [
            "url"=> "http://example.com",
            "uuid"=> "0a3a0ffb-dc03-4aae-b207-0ed1502e60da",
            "identificatie"=> "string",
            "bronorganisatie"=> "string",
            "omschrijving"=> "Zaak 4",
            "toelichting"=> "string",
            "zaaktype"=> "http://example.com",
            "registratiedatum"=> "2019-08-24",
            "verantwoordelijkeOrganisatie"=> "string",
            "startdatum"=> "2019-08-24",
            "einddatum"=> "2019-08-24",
            "einddatumGepland"=> "2019-08-24",
            "uiterlijkeEinddatumAfdoening"=> "2019-08-24",
            "publicatiedatum"=> "2019-08-24",
            "communicatiekanaal"=> "http://example.com",
            "betalingsindicatie"=>"nvt",
            "betalingsindicatieWeergave"=> "string",
            "laatsteBetaaldatum"=> "2019-08-24T14:15:22Z",
            "selectielijstklasse"=> "http://example.com",
            "hoofdzaak"=> "http://example.com",
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
