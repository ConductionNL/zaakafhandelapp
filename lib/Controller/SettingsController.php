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
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class SettingsController extends Controller
{

    /**
     * Exhaustive allow-list of keys accepted on POST.
     *
     * Derived from OBJECT_TYPES: each type contributes three keys (_source, _schema, _register).
     * Unlisted keys are silently ignored to prevent admin footgun on arbitrary appconfig writes.
     *
     * @var string[]
     */
    private const WRITABLE_KEYS = [
        'berichten_source',
        'berichten_schema',
        'berichten_register',
        'besluiten_source',
        'besluiten_schema',
        'besluiten_register',
        'documenten_source',
        'documenten_schema',
        'documenten_register',
        'klanten_source',
        'klanten_schema',
        'klanten_register',
        'resultaten_source',
        'resultaten_schema',
        'resultaten_register',
        'taken_source',
        'taken_schema',
        'taken_register',
        'informatieobjecten_source',
        'informatieobjecten_schema',
        'informatieobjecten_register',
        'organisaties_source',
        'organisaties_schema',
        'organisaties_register',
        'personen_source',
        'personen_schema',
        'personen_register',
        'zaken_source',
        'zaken_schema',
        'zaken_register',
        'rollen_source',
        'rollen_schema',
        'rollen_register',
        'statusen_source',
        'statusen_schema',
        'statusen_register',
        'zaakeigenschappen_source',
        'zaakeigenschappen_schema',
        'zaakeigenschappen_register',
        'zaaktypen_source',
        'zaaktypen_schema',
        'zaaktypen_register',
        'contactmomenten_source',
        'contactmomenten_schema',
        'contactmomenten_register',
        'medewerkers_source',
        'medewerkers_schema',
        'medewerkers_register',
        'producten_source',
        'producten_schema',
        'producten_register',
    ];

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
     * @param string              $appName       The name of the app
     * @param IRequest            $request       The request object
     * @param IAppConfig          $config        The app configuration
     * @param ObjectMapperService $mapperService The mapper service
     */
    public function __construct(
        $appName,
        IRequest $request,
        private readonly IAppConfig $config,
        private readonly ObjectMapperService $mapperService
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Retrieve the current settings.
     *
     * @return JSONResponse JSON response containing the current settings
     *
     * @NoCSRFRequired
     *
     * @spec openspec/specs/app-configuration/spec.md#REQ-002
     */
    public function index(): JSONResponse
    {
        $data = [];
        $data['objectTypes'] = array_slice(self::OBJECT_TYPES, 0, -1);
        // Exclude 'producten' from visible list
        $data['openRegisters']      = false;
        $data['availableRegisters'] = [];

        $openRegisters = $this->mapperService->getOpenRegisters();
        if ($openRegisters !== null) {
            $data['openRegisters']      = true;
            $data['availableRegisters'] = $this->mapperService->getRegisters();
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
    }//end index()

    /**
     * Handle the post request to update settings.
     *
     * Only keys present in WRITABLE_KEYS are accepted; all others are silently ignored.
     * Values are cast to string to avoid TypeError from array inputs on setValueString().
     *
     * @return JSONResponse JSON response containing the updated settings
     *
     * @NoCSRFRequired
     *
     * @spec openspec/specs/app-configuration/spec.md#REQ-002
     */
    public function create(): JSONResponse
    {
        $requestData = $this->request->getParams();

        try {
            $data = [];
            foreach (self::WRITABLE_KEYS as $key) {
                if (array_key_exists($key, $requestData) === false) {
                    continue;
                }

                $this->config->setValueString($this->appName, $key, (string) $requestData[$key]);
                $data[$key] = $this->config->getValueString($this->appName, $key);
            }

            return new JSONResponse($data);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }//end create()

    /**
     * Build the defaults array for all object type settings.
     *
     * @return array The defaults with keys like '{type}_source', '{type}_schema', '{type}_register'
     */
    private function buildDefaults(): array
    {
        $defaults = [];

        foreach (self::OBJECT_TYPES as $type) {
            $defaults[$type.'_source']   = 'internal';
            $defaults[$type.'_schema']   = '';
            $defaults[$type.'_register'] = '';
        }

        return $defaults;
    }//end buildDefaults()
}//end class
