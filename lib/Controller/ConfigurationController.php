<?php
/**
 * Controller for handling configuration operations.
 *
 * @category Controller
 * @package  OCA\ZaakAfhandelApp\Controller
 * @author   Conduction b.v. <info@conduction.nl>
 * @license  EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link     https://conduction.nl
 */

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

/**
 * Controller for managing application configuration settings.
 */
class ConfigurationController extends Controller
{
    /**
     * Constructor for ConfigurationController.
     *
     * @param string     $appName The app name.
     * @param IAppConfig $config  The app configuration.
     * @param IRequest   $request The request object.
     */
    public function __construct(
        $appName,
        private readonly IAppConfig $config,
        IRequest $request
    ) {
        parent::__construct(appName: $appName, request: $request);
    }//end __construct()

    /**
     * Retrieve the current configuration.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function index(): JSONResponse
    {

        $data     = [];
        $defaults = [
        // Getting the config.
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

        // We should filter out unwanted values before this.
        foreach ($defaults as $key => $value) {
            $data[$key] = $this->config->getValueString('zaakafhandelapp', $key, $value);
        }

        return new JSONResponse($data);
    }//end index()

    /**
     * Handle the post request to update configuration.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function create(): JSONResponse
    {
        $data = $this->request->getParams();

        // We should filter out unwanted values before this.
        foreach ($data as $key => $value) {
            $this->config->setValueString('zaakafhandelapp', $key, $value);
            $data[$key] = $this->config->getValueString('zaakafhandelapp', $key);
        }

        return new JSONResponse($data);
    }//end create()
}//end class
