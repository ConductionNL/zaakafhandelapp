<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Controller for application configuration operations.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ConfigurationController extends Controller
{
    public function __construct(
        $appName,
        private readonly IAppConfig $config,
        IRequest $request,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
      *
      * @spec openspec/specs/app-configuration/spec.md#REQ-001
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $data     = [];
        $defaults = [
        // Getting the config
            'drcLocation'      => '',
            'drcKey'           => '',
            'drcAuthType'      => '',
            'orcLocation'      => '',
            'orcKey'           => '',
            'orcAuthType'      => '',
            'zrcLocation'      => '',
            'zrcKey'           => '',
            'zrcAuthType'      => '',
            'ztcLocation'      => '',
            'ztcKey'           => '',
            'ztcAuthType'      => '',
            'brcLocation'      => '',
            'brcKey'           => '',
            'brcAuthType'      => '',
            'klantenLocation'  => '',
            'klantenKey'       => '',
            'klantenAuthType'  => '',
            'elasticLocation'  => '',
            'elasticKey'       => '',
            'mongodbLocation'  => '',
            'mongodbKey'       => '',
            'mongodbCluster'   => '',
            'organisationName' => '',
            'organisationOIN'  => '',
            'organisationPKI'  => '',
            'organisationRSIN' => '',
            'organisationKVK'  => '',
        ];

        // We should filter out unwanted values before this
        foreach ($defaults as $key => $value) {
            $data[$key] = $this->config->getValueString('zaakafhandelapp', $key, $value);
        }

        return new JSONResponse($data);
    }//end index()

    /**
     * Handling the post request
     *
     * @NoAdminRequired
     * @NoCSRFRequired
      *
      * @spec openspec/specs/app-configuration/spec.md#REQ-001
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $data = $this->request->getParams();

        // We should filter out unwanted values before this
        foreach ($data as $key => $value) {
            $this->config->setValueString('zaakafhandelapp', $key, $value);
            $data[$key] = $this->config->getValueString('zaakafhandelapp', $key);
        }

        return new JSONResponse($data);
    }//end create()
}//end class
