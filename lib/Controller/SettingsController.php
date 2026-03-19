<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\IAppConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;

/**
 * Class SettingsController
 *
 * Controller for handling settings-related operations in the ZaakAfhandelApp.
 */
class SettingsController extends Controller
{

	/**
	 * Object types that have configurable source, schema and register settings.
	 */
	private const OBJECT_TYPES = [
		'berichten',
		'besluiten',
		'documenten',
		'klanten',
		'resultaten',
		'taken',
		'informatieobjecten',
		'organisaties',
		'personen',
		'zaken',
		'rollen',
		'statusen',
		'zaakeigenschappen',
		'zaaktypen',
		'contactmomenten',
		'medewerkers',
		'producten',
	];

	/**
	 * SettingsController constructor.
	 *
	 * @param string $appName The name of the app
	 * @param IRequest $request The request object
	 * @param IAppConfig $config The app configuration
	 * @param ObjectMapperService $mapperService The mapper service
	 */
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IAppConfig $config,
		private readonly ObjectMapperService $mapperService
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
		$data = [];
		$data['objectTypes'] = array_slice(self::OBJECT_TYPES, 0, -1); // Exclude 'producten' from visible list
		$data['openRegisters'] = false;
		$data['availableRegisters'] = [];

		$openRegisters = $this->mapperService->getOpenRegisters();
		if ($openRegisters !== null) {
			$data['openRegisters'] = true;
			$data['availableRegisters'] = $openRegisters->getRegisters();
		}

		$defaults = $this->buildDefaults();

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
		$data = $this->request->getParams();

		try {
			foreach ($data as $key => $value) {
				$this->config->setValueString($this->appName, $key, $value);
				$data[$key] = $this->config->getValueString($this->appName, $key);
			}
			return new JSONResponse($data);
		} catch (\Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Build the defaults array for all object type settings.
	 *
	 * @return array The defaults with keys like '{type}_source', '{type}_schema', '{type}_register'
	 */
	private function buildDefaults(): array
	{
		$defaults = [];

		foreach (self::OBJECT_TYPES as $type) {
			$defaults[$type . '_source'] = 'internal';
			$defaults[$type . '_schema'] = '';
			$defaults[$type . '_register'] = '';
		}

		return $defaults;
	}
}
