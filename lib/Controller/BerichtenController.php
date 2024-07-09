<?php

namespace OCA\ZaakAfhandelApp\Controller;

use GuzzleHttp\Client;
use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class BerichtenController extends Controller
{
    const TEST_ARRAY = [
        "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f" => [
            "id" => "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
            "batchID" => "f7eebefe-52e6-4b60-a80a-47d6f1cbd5bf",
            "aanmaakDatum" => "2023-07-08T12:00:00Z",
            "berichtLeverancierID" => "12345678901234567890",
            "berichtID" => "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
            "berichtType" => "Type1",
            "publicatieDatum" => "2023-07-10T12:00:00Z",
            "onderwerp" => "Onderwerp 1",
            "berichttekst" => "Dit is de tekst van bericht 1.",
            "referentie" => "Ref1",
            "gebruikerID" => "987654321",
            "soortGebruiker" => "Burger",
            "inhoud" => "VGhpcyBpcyBhIHRlc3QgcGRmIGZpbGUu",
            "bijlageType" => "Pdf",
            "omschrijving" => "Omschrijving voor bijlage 1",
            "volgorde" => "1"
        ],
        "4c3edd34-a90d-4d2a-8894-adb5836ecde8" => [
            "id" => "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
            "batchID" => "c9d3a2e9-bb4e-4212-a55c-9a8e8376d5c5",
            "aanmaakDatum" => "2023-07-09T12:00:00Z",
            "berichtLeverancierID" => "22345678901234567890",
            "berichtID" => "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
            "berichtType" => "Type2",
            "publicatieDatum" => "2023-07-11T12:00:00Z",
            "onderwerp" => "Onderwerp 2",
            "berichttekst" => "Dit is de tekst van bericht 2.",
            "referentie" => "Ref2",
            "gebruikerID" => "876543210",
            "soortGebruiker" => "Burger",
            "inhoud" => "VGhpcyBpcyBhbm90aGVyIHRlc3QgcGRmIGZpbGUu",
            "bijlageType" => "Pdf",
            "omschrijving" => "Omschrijving voor bijlage 2",
            "volgorde" => "2"
        ],
        "15551d6f-44e3-43f3-a9d2-59e583c91eb0" => [
            "id" => "15551d6f-44e3-43f3-a9d2-59e583c91eb0",
            "batchID" => "a7d3b4e5-d64f-499a-96e5-c2bf9f5c7d2c",
            "aanmaakDatum" => "2023-07-10T12:00:00Z",
            "berichtLeverancierID" => "32345678901234567890",
            "berichtID" => "15551d6f-44e3-43f3-a9d2-59e583c91eb0",
            "berichtType" => "Type3",
            "publicatieDatum" => "2023-07-12T12:00:00Z",
            "onderwerp" => "Onderwerp 3",
            "berichttekst" => "Dit is de tekst van bericht 3.",
            "referentie" => "Ref3",
            "gebruikerID" => "765432109",
            "soortGebruiker" => "Burger",
            "inhoud" => "VGhpcyBpcyB5ZXQgYW5vdGhlciB0ZXN0IHBkZiBmaWxlLg==",
            "bijlageType" => "Pdf",
            "omschrijving" => "Omschrijving voor bijlage 3",
            "volgorde" => "3"
        ],
        "0a3a0ffb-dc03-4aae-b207-0ed1502e60da" => [
            "id" => "0a3a0ffb-dc03-4aae-b207-0ed1502e60da",
            "batchID" => "b7c3e5f9-ec2f-4dbb-b602-e7af1f7e5c4d",
            "aanmaakDatum" => "2023-07-11T12:00:00Z",
            "berichtLeverancierID" => "42345678901234567890",
            "berichtID" => "0a3a0ffb-dc03-4aae-b207-0ed1502e60da",
            "berichtType" => "Type4",
            "publicatieDatum" => "2023-07-13T12:00:00Z",
            "onderwerp" => "Onderwerp 4",
            "berichttekst" => "Dit is de tekst van bericht 4.",
            "referentie" => "Ref4",
            "gebruikerID" => "654321098",
            "soortGebruiker" => "Burger",
            "inhoud" => "VGhpcyBpcyBhbm90aGVyIHRlc3QgcGRmIGZpbGUu",
            "bijlageType" => "Pdf",
            "omschrijving" => "Omschrijving voor bijlage 4",
            "volgorde" => "4"
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
	public function index(CallService $callService): JSONResponse
	{
		// Latere zorg
		$query= $this->request->getParams();

		$results = $callService->index(source: 'klanten', endpoint: 'taken');
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
	public function show(string $id, CallService $callService): JSONResponse
	{
		// Latere zorg
		$query= $this->request->getParams();

		$results = $callService->show(source: 'klanten', endpoint: 'taken', id: $id);
		return new JSONResponse($results);
	}


	/**
	 * Creatue an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function create(CallService $callService): JSONResponse
	{
		// get post from requests
		$body = $this->request->getParams();
		$results = $callService->create(source: 'klanten', endpoint: 'taken', data: $body);
		return new JSONResponse($results);
	}

	/**
	 * Update an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function update(string $id, CallService $callService): JSONResponse
	{
		$body = $this->request->getParams();
		$results = $callService->update(source: 'klanten', endpoint: 'taken', data: $body, id: $id);
		return new JSONResponse($results);
	}

	/**
	 * Delate an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function destroy(string $id, CallService $callService): JSONResponse
	{
		$callService->destroy(source: 'klanten', endpoint: 'taken', id: $id);

		return new JsonResponse([]);
	}
}
