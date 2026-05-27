<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

/**
 * Controller for reading and writing ZGW service configuration.
 *
 * Both GET and POST require admin privileges: the configuration contains
 * service-account API keys that must never be exposed to regular users.
 */
class ConfigurationController extends Controller
{
    /**
     * Keys that hold credentials — returned as "***" on read.
     */
    private const CREDENTIAL_KEYS = [
        'drcKey',
        'orcKey',
        'zrcKey',
        'ztcKey',
        'brcKey',
        'klantenKey',
        'elasticKey',
        'mongodbKey',
    ];

    /**
     * Exhaustive allow-list of keys that may be written via POST.
     */
    private const WRITABLE_KEYS = [
        'drcLocation',
        'drcKey',
        'drcAuthType',
        'orcLocation',
        'orcKey',
        'orcAuthType',
        'zrcLocation',
        'zrcKey',
        'zrcAuthType',
        'ztcLocation',
        'ztcKey',
        'ztcAuthType',
        'brcLocation',
        'brcKey',
        'brcAuthType',
        'klantenLocation',
        'klantenKey',
        'klantenAuthType',
        'elasticLocation',
        'elasticKey',
        'mongodbLocation',
        'mongodbKey',
        'mongodbCluster',
        'organisationName',
        'organisationOIN',
        'organisationPKI',
        'organisationRSIN',
        'organisationKVK',
    ];

    public function __construct(
        $appName,
        private readonly IAppConfig $config,
        IRequest $request
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Return the current configuration.
     *
     * Credential fields are redacted: only their presence (non-empty) is
     * indicated. This prevents exfiltration of API keys.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function index(): JSONResponse
    {
        $defaults = array_fill_keys(self::WRITABLE_KEYS, '');

        $data = [];
        foreach ($defaults as $key => $default) {
            $value = $this->config->getValueString('zaakafhandelapp', $key, $default);
            // Redact credential values so they are never transmitted to the client.
            if (in_array($key, self::CREDENTIAL_KEYS, true)) {
                $data[$key] = $value !== '' ? '***' : '';
            } else {
                $data[$key] = $value;
            }
        }

        return new JSONResponse($data);
    }//end index()

    /**
     * Persist configuration values supplied by an admin.
     *
     * Only keys present in WRITABLE_KEYS are accepted; all other keys in the
     * request body are silently ignored.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function create(): JSONResponse
    {
        $requestData = $this->request->getParams();

        $data = [];
        foreach (self::WRITABLE_KEYS as $key) {
            if (!array_key_exists($key, $requestData)) {
                continue;
            }

            $this->config->setValueString('zaakafhandelapp', $key, (string) $requestData[$key]);
            // Redact credential values in the response.
            if (in_array($key, self::CREDENTIAL_KEYS, true)) {
                $data[$key] = '***';
            } else {
                $data[$key] = $this->config->getValueString('zaakafhandelapp', $key);
            }
        }

        return new JSONResponse($data);
    }//end create()
}//end class
