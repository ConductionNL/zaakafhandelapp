<?php

namespace OCA\DsoNextcloud\Controller;

use OCP\IAppConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

use OCA\DsoNextcloud\AppInfo\Application;

class ConfigurationController extends Controller
{
    const TEST_ARRAY = [
        "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f" => [
            "id" => "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
            "name" => "Github",
            "summary" => "summary for one"
        ],
        "4c3edd34-a90d-4d2a-8894-adb5836ecde8" => [
            "id" => "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
            "name" => "Gitlab",
            "summary" => "summary for two"
        ],
        "15551d6f-44e3-43f3-a9d2-59e583c91eb0" => [
            "id" => "15551d6f-44e3-43f3-a9d2-59e583c91eb0",
            "name" => "Woo",
            "summary" => "summary for two"
        ],
        "0a3a0ffb-dc03-4aae-b207-0ed1502e60da" => [
            "id" => "0a3a0ffb-dc03-4aae-b207-0ed1502e60da",
            "name" => "Decat",
            "summary" => "summary for two"
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
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function index(): TemplateResponse
	{
		return new TemplateResponse(Application::APP_ID, 'configuration', []);
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function show(): JSONResponse
	{
		// Getting the config
		$zakenLocation = $this->config->getValueString(Application::APP_ID, 'zaken_location', '');
		$zakenKey = $this->config->getValueString(Application::APP_ID, 'zaken_key', '');
		$takenLocation = $this->config->getValueString(Application::APP_ID, 'taken_location', '');
		$takenKey = $this->config->getValueString(Application::APP_ID, 'taken_key', '');
		$contactMomentenLocation = $this->config->getValueString(Application::APP_ID, 'contact_momenten_location', '');
		$klantenLocation = $this->config->getValueString(Application::APP_ID, 'klanten_location', '');
		$klantenKey = $this->config->getValueString(Application::APP_ID, 'klanten_key', '');
		$zaakTypenLocation = $this->config->getValueString(Application::APP_ID, 'zaak_typen_location', '');
		$zaakTypenKey = $this->config->getValueString(Application::APP_ID, 'zaak_typen_key', '');

		$data = [
			'zakenLocation' => $zakenLocation,
			'zakenKey' => $zakenKey,
			'takenLocation' => $takenLocation,
			'takenKey' => $takenKey,
			'contactMomentenLocation' => $contactMomentenLocation,
			'klantenLocation' => $klantenLocation,
			'klantenKey' => $klantenKey,
			'zaakTypenLocation' => $zaakTypenLocation,
			'zaakTypenKey' => $zaakTypenKey,
		];

		return new JSONResponse($data);
	}



	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function updata(): JSONResponse
	{
		$data = $this->request->getParam();

		$zakenLocation = $this->config->setValueString(Application::APP_ID, 'zaken_location', $data['zakenLocation']);
		$zakenKey = $this->config->setValueString(Application::APP_ID, 'zaken_key', $data['zakenKey']);
		$takenLocation = $this->config->setValueString(Application::APP_ID, 'taken_location', $data['takenLocation']);
		$takenKey = $this->config->setValueString(Application::APP_ID, 'taken_key', $data['takenKey']);
		$contactMomentenLocation = $this->config->setValueString(Application::APP_ID, 'contact_momenten_location', $data['contactMomentenLocation']);
		$klantenLocation = $this->config->setValueString(Application::APP_ID, 'klanten_location', $data['klantenLocation']);
		$klantenKey = $this->config->setValueString(Application::APP_ID, 'klanten_key', $data['klantenKey']);
		$zaakTypenLocation = $this->config->setValueString(Application::APP_ID, 'zaak_typen_location', $data['zaakTypenLocation']);
		$zaakTypenKey = $this->config->setValueString(Application::APP_ID, 'zaak_typen_key', $data['zaakTypenKey']);


		// Getting the config
		$zakenLocation = $this->config->getValueString(Application::APP_ID, 'zaken_location', '');
		$zakenKey = $this->config->getValueString(Application::APP_ID, 'zaken_key', '');
		$takenLocation = $this->config->getValueString(Application::APP_ID, 'taken_location', '');
		$takenKey = $this->config->getValueString(Application::APP_ID, 'taken_key', '');
		$contactMomentenLocation = $this->config->getValueString(Application::APP_ID, 'contact_momenten_location', '');
		$klantenLocation = $this->config->getValueString(Application::APP_ID, 'klanten_location', '');
		$klantenKey = $this->config->getValueString(Application::APP_ID, 'klanten_key', '');
		$zaakTypenLocation = $this->config->getValueString(Application::APP_ID, 'zaak_typen_location', '');
		$zaakTypenKey = $this->config->getValueString(Application::APP_ID, 'zaak_typen_key', '');

		$data = [
			'zakenLocation' => $zakenLocation,
			'zakenKey' => $zakenKey,
			'takenLocation' => $takenLocation,
			'takenKey' => $takenKey,
			'contactMomentenLocation' => $contactMomentenLocation,
			'klantenLocation' => $klantenLocation,
			'klantenKey' => $klantenKey,
			'zaakTypenLocation' => $zaakTypenLocation,
			'zaakTypenKey' => $zaakTypenKey,
		];

		return new JSONResponse($data);
	}
}
