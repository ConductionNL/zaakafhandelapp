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

	/**
	 * @var IConfig
	 */
	private $config;


	public function __construct(
		string $appName,
		IRequest $request,
		IAppConfig $config
	) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->request = $request;
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
