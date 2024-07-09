<?php

namespace OCA\ZaakAfhandelApp\Controller;

use GuzzleHttp\Client;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class KlantenController extends Controller
{

    const TEST_ARRAY = [
        "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f" => [
            "id" => "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
            "klantnummer" => "10001234",
            "voornaam" => "Jan",
            "voorvoegsel" => "van",
            "achternaam" => "Dijk",
            "telefoonnummer" => "0612345678",
            "emailadres" => "jan.vandijk@example.com",
            "adres" => "Kerkstraat 12, 1017 GX Amsterdam",
            "aanmaakkanaal" => "Website",
            "functie" => "Manager",
            "bronorganisatie" => "ABC BV",
            "bedrijfsnaam" => "ABC BV",
            "websiteUrl" => "http://www.abcbv.nl",
            "url" => "http://www.abcbv.nl/janv",
            "geverifieerd" => "Ja",
            "subject" => "Klantenbeheer",
            "subjectIdentificatie" => "123456",
            "subjectType" => "Zakelijk"
        ],
        "4c3edd34-a90d-4d2a-8894-adb5836ecde8" => [
            "id" => "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
            "klantnummer" => "10005678",
            "voornaam" => "Maria",
            "voorvoegsel" => "de",
            "achternaam" => "Jong",
            "telefoonnummer" => "0687654321",
            "emailadres" => "maria.dejong@example.com",
            "adres" => "Hoofdstraat 34, 1234 AB Utrecht",
            "aanmaakkanaal" => "Telefoon",
            "functie" => "Directeur",
            "bronorganisatie" => "XYZ NV",
            "bedrijfsnaam" => "XYZ NV",
            "websiteUrl" => "http://www.xyznv.nl",
            "url" => "http://www.xyznv.nl/mariad",
            "geverifieerd" => "Nee",
            "subject" => "Marketing",
            "subjectIdentificatie" => "789012",
            "subjectType" => "Zakelijk"
        ],
        "15551d6f-44e3-43f3-a9d2-59e583c91eb0" => [
            "id" => "15551d6f-44e3-43f3-a9d2-59e583c91eb0",
            "klantnummer" => "10007890",
            "voornaam" => "Peter",
            "voorvoegsel" => "van",
            "achternaam" => "den Berg",
            "telefoonnummer" => "0611122233",
            "emailadres" => "peter.vandenberg@example.com",
            "adres" => "Markt 5, 5678 CD Rotterdam",
            "aanmaakkanaal" => "Email",
            "functie" => "Sales",
            "bronorganisatie" => "DEF Ltd",
            "bedrijfsnaam" => "DEF Ltd",
            "websiteUrl" => "http://www.defltd.com",
            "url" => "http://www.defltd.com/peterb",
            "geverifieerd" => "Ja",
            "subject" => "Verkoop",
            "subjectIdentificatie" => "345678",
            "subjectType" => "Zakelijk"
        ],
        "0a3a0ffb-dc03-4aae-b207-0ed1502e60da" => [
            "id" => "0a3a0ffb-dc03-4aae-b207-0ed1502e60da",
            "klantnummer" => "10004321",
            "voornaam" => "Sophie",
            "voorvoegsel" => "van",
            "achternaam" => "Loon",
            "telefoonnummer" => "0644556677",
            "emailadres" => "sophie.vanloon@example.com",
            "adres" => "Laan 7, 8765 EF Den Haag",
            "aanmaakkanaal" => "Mobiel",
            "functie" => "IT Specialist",
            "bronorganisatie" => "GHI Corp",
            "bedrijfsnaam" => "GHI Corp",
            "websiteUrl" => "http://www.ghicorp.com",
            "url" => "http://www.ghicorp.com/sophiev",
            "geverifieerd" => "Nee",
            "subject" => "Technologie",
            "subjectIdentificatie" => "901234",
            "subjectType" => "Zakelijk"
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
