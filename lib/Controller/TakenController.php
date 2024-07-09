<?php

namespace OCA\ZaakAfhandelApp\Controller;

use GuzzleHttp\Client;
use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class TakenController extends Controller
{
    const TEST_ARRAY = [
        "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f" => [
            "id" => "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
            "title" => "Onderzoek naar Markttrends",
            "zaak" => "Marktanalyse",
            "type" => "Onderzoek",
            "status" => "open",
            "onderwerp" => "Analyse van de huidige markttrends en voorspellingen voor het komende jaar.",
            "toelichting" => "Dit onderzoek richt zich op het analyseren van de huidige markttrends in de technologie-sector. Het bevat gedetailleerde gegevens en grafieken die de groei en dalingen in verschillende sub-sectoren weergeven, evenals voorspellingen voor de komende 12 maanden.",
            "actie" => "Verzamelen van gegevens, opstellen van rapporten, presenteren aan het management."
        ],
        "4c3edd34-a90d-4d2a-8894-adb5836ecde8" => [
            "id" => "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
            "title" => "Ontwikkeling van Nieuw Product",
            "zaak" => "Productinnovatie",
            "type" => "Ontwikkeling",
            "status" => "ingediend",
            "onderwerp" => "Concept en ontwikkeling van een nieuw innovatief product voor de consumentenmarkt.",
            "toelichting" => "Dit project omvat de conceptfase en de vroege ontwikkeling van een nieuw product dat gericht is op het verbeteren van de consumentenervaring in de smart home sector. Het omvat marktonderzoek, productontwerp en het ontwikkelen van een prototype.",
            "actie" => "Uitvoeren van marktonderzoek, samenwerken met het designteam, ontwikkelen van een prototype, testen van het product."
        ],
        "15551d6f-44e3-43f3-a9d2-59e583c91eb0" => [
            "id" => "15551d6f-44e3-43f3-a9d2-59e583c91eb0",
            "title" => "Interne Audit",
            "zaak" => "Kwaliteitscontrole",
            "type" => "Audit",
            "status" => "verwerkt",
            "onderwerp" => "Uitvoering van een interne audit om de naleving van kwaliteitsnormen te controleren.",
            "toelichting" => "Deze taak omvat het uitvoeren van een gedetailleerde interne audit van de bedrijfsprocessen om te controleren of alle afdelingen voldoen aan de vastgestelde kwaliteitsnormen. De bevindingen worden gedocumenteerd en er worden aanbevelingen gedaan voor verbeteringen.",
            "actie" => "Voorbereiden van auditchecklist, uitvoeren van audits, rapporteren van bevindingen, aanbevelen van verbeteringen."
        ],
        "0a3a0ffb-dc03-4aae-b207-0ed1502e60da" => [
            "id" => "0a3a0ffb-dc03-4aae-b207-0ed1502e60da",
            "title" => "Marketingcampagne voor Nieuwe Productlijn",
            "zaak" => "Marketingstrategie",
            "type" => "Campagne",
            "status" => "gesloten",
            "onderwerp" => "Ontwikkeling en lancering van een marketingcampagne voor een nieuwe productlijn.",
            "toelichting" => "Deze taak omvat het plannen en uitvoeren van een marketingcampagne voor de lancering van een nieuwe productlijn. Het omvat het bepalen van de doelgroep, het ontwikkelen van marketingmateriaal, het inzetten van verschillende marketingkanalen en het monitoren van de resultaten.",
            "actie" => "Bepalen van doelgroep, ontwikkelen van marketingmateriaal, uitvoeren van campagne, analyseren van resultaten."
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
	public function index(CallService $klantenService): JSONResponse
	{
		// Latere zorg
		$query= $this->request->getParams();

		$results = $klantenService->index(source: 'klanten', endpoint: 'taken');
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
	public function show(string $id, CallService $klantenService): JSONResponse
	{
		// Latere zorg
		$query= $this->request->getParams();

		$results = $klantenService->show(source: 'klanten', endpoint: 'taken', id: $id);
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
