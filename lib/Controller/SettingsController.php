<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\IAppConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\ZaakAfhandelApp\Service\ObjectService;

/**
 * Class SettingsController
 *
 * Controller for handling settings-related operations in the OpenCatalogi app.
 */
class SettingsController extends Controller
{

	/**
	 * SettingsController constructor.
	 *
	 * @param string $appName The name of the app
	 * @param IAppConfig $config The app configuration
	 * @param IRequest $request The request object
	 * @param ObjectService $objectService The object service
	 */
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IAppConfig $config,
		private readonly ObjectService $objectService
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Retrieve the current settings.
	 *
	 * @return JSONResponse JSON response containing the current settings
	 *
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse
	{
		// Initialize the data array
		$data = [];
		$data['objectTypes'] = ['berichten', 'besluiten', 'documenten', 'klanten', 'resultaten', 'taken', 'informatieobjecten', 'organisaties', 'personen', 'zaken', 'rollen', 'statusen', 'zaakeigenschappen', 'zaaktypen','contactmomenten', 'medewerkers'];
		$data['openRegisters'] = false;
		$data['availableRegisters'] = [];

		// Check if the OpenRegister service is available
		$openRegisters = $this->objectService->getOpenRegisters();
		if ($openRegisters !== null) {
			$data['openRegisters'] = true;
			$data['availableRegisters'] = $openRegisters->getRegisters();
		}

		// Define the default values for the object types
		$defaults = [
			'berichten_source' => 'internal',
			'berichten_schema' => '',
			'berichten_register' => '',
			'besluiten_source' => 'internal',
			'besluiten_schema' => '',
			'besluiten_register' => '',
			'documenten_source' => 'internal',
			'documenten_schema' => '',
			'documenten_register' => '',
			'klanten_source' => 'internal',
			'klanten_schema' => '',
			'klanten_register' => '',
			'resultaten_source' => 'internal',
			'resultaten_schema' => '',
			'resultaten_register' => '',
			'taken_source' => 'internal',
			'taken_schema' => '',
			'taken_register' => '',
			'informatieobjecten_source' => 'internal',
			'informatieobjecten_schema' => '',
			'informatieobjecten_register' => '',
			'organisaties_source' => 'internal',
			'organisaties_schema' => '',
			'organisaties_register' => '',
			'personen_source' => 'internal',
			'personen_schema' => '',
			'personen_register' => '',
			'zaken_source' => 'internal',
			'zaken_schema' => '',
			'zaken_register' => '',
			'rollen_source' => 'internal',
			'rollen_schema' => '',
			'rollen_register' => '',
			'statusen_source' => 'internal',
			'statusen_schema' => '',
			'statusen_register' => '',
			'zaakeigenschappen_source' => 'internal',
			'zaakeigenschappen_schema' => '',
			'zaakeigenschappen_register' => '',
			'zaaktypen_source' => 'internal',
			'zaaktypen_schema' => '',
			'zaaktypen_register' => '',
			'contactmomenten_source' => 'internal',
			'contactmomenten_schema' => '',
			'contactmomenten_register' => '',
			'medewerkers_source' => 'internal',
			'medewerkers_schema' => '',
			'medewerkers_register' => '',
			'producten_source' => 'internal',
			'producten_schema' => '',
			'producten_register' => '',
		];

		// Get the current values for the object types from the configuration
		try {
			foreach ($defaults as $key => $value) {
				$data[$key] = $this->config->getValueString($this->appName, $key, $value);
			}
			return new JSONResponse($data);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Handle the post request to update settings.
	 *
	 * @return JSONResponse JSON response containing the updated settings
	 *
	 * @NoCSRFRequired
	 */
	public function create(): JSONResponse
	{
		// Get all parameters from the request
		$data = $this->request->getParams();

		try {
			// Update each setting in the configuration
			foreach ($data as $key => $value) {
				$this->config->setValueString($this->appName, $key, $value);
				// Retrieve the updated value to confirm the change
				$data[$key] = $this->config->getValueString($this->appName, $key);
			}
			return new JSONResponse($data);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}
}
