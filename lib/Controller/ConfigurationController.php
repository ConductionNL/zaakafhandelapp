<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Controller for application configuration operations.
 *
 * Both GET and POST require admin: the configuration holds ZGW service-account
 * API keys that must never be exposed to non-admin users (issue #267).
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ConfigurationController extends Controller
{
    /**
     * Keys that hold credentials — returned as "***" on read.
     *
     * @var string[]
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
     * Exhaustive allow-list of keys accepted on POST.
     *
     * @var string[]
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
        IRequest $request,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Return the current configuration.
     *
     * Credential fields are redacted (returned as "***") so that API keys are
     * never transmitted to the browser. Admin-only: @NoAdminRequired omitted.
     *
     * @NoCSRFRequired
     *
     * @spec openspec/specs/app-configuration/spec.md#REQ-001
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $defaults = array_fill_keys(self::WRITABLE_KEYS, '');

        $data = [];
        foreach ($defaults as $key => $default) {
            $value      = $this->config->getValueString('zaakafhandelapp', $key, $default);
            $data[$key] = in_array($key, self::CREDENTIAL_KEYS, true) ? ($value !== '' ? '***' : '') : $value;
        }

        return new JSONResponse($data);
    }//end index()

    /**
     * Persist configuration values supplied by an admin.
     *
     * Only keys present in WRITABLE_KEYS are accepted; all others are ignored.
     * Credential values are redacted in the response. Admin-only: @NoAdminRequired omitted.
     *
     * @NoCSRFRequired
     *
     * @spec openspec/specs/app-configuration/spec.md#REQ-001
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $requestData = $this->request->getParams();

        $data = [];
        foreach (self::WRITABLE_KEYS as $key) {
            if (!array_key_exists($key, $requestData)) {
                continue;
            }

            $this->config->setValueString('zaakafhandelapp', $key, (string) $requestData[$key]);
            $data[$key] = in_array($key, self::CREDENTIAL_KEYS, true)
                ? '***'
                : $this->config->getValueString('zaakafhandelapp', $key);
        }

        return new JSONResponse($data);
    }//end create()
}//end class
