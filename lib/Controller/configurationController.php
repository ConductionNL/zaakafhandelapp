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
	 * This returns the template of the main app's page
	 * It adds some data to the template (app version)
	 *
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function api(): JSONResponse
	{
		// Handle POST
		if($this->request->getMethod() === "POST"){

			
			$zakenLocation = $this->config->setValueString(Application::APP_ID, 'zaken_location', '');
			$zakenKey = $this->config->setValueString(Application::APP_ID, 'zaken_key', '');
			$takenLocation = $this->config->setValueString(Application::APP_ID, 'taken_location', '');
			$takenKey = $this->config->setValueString(Application::APP_ID, 'taken_key', '');
			$contactMomentenLocation = $this->config->setValueString(Application::APP_ID, 'contact_momenten_location', '');
			$klantenLocation = $this->config->setValueString(Application::APP_ID, 'klanten_location', '');
			$klantenKey = $this->config->setValueString(Application::APP_ID, 'klanten_key', '');
			$zaakTypenLocation = $this->config->setValueString(Application::APP_ID, 'zaak_typen_location', '');
			$zaakTypenKey = $this->config->setValueString(Application::APP_ID, 'zaak_typen_key', '');
		}


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
