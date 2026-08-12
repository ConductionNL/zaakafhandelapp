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
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ConfigurationController extends Controller {
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

	/**
	 * Build the configuration controller.
	 *
	 * @param string $appName The application name.
	 * @param IAppConfig $config The app configuration store the values are read from and written to.
	 * @param IRequest $request The current HTTP request.
	 * @param IUserSession $userSession Session used to resolve the acting user.
	 */
	public function __construct(
		$appName,
		private readonly IAppConfig $config,
		IRequest $request,
		private readonly IUserSession $userSession,
	) {
		parent::__construct(appName: $appName, request: $request);
	}//end __construct()

	/**
	 * Return the current configuration.
	 *
	 * Credential fields are redacted (returned as "***") so that API keys are
	 * never transmitted to the browser. Admin-only: @NoAdminRequired omitted.
	 *
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse The configuration keys with credential values redacted.
	 *
	 * @spec openspec/specs/app-configuration/spec.md#REQ-001
	 */
	public function index(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$defaults = array_fill_keys(self::WRITABLE_KEYS, '');

		$data = [];
		foreach ($defaults as $key => $default) {
			$value = $this->config->getValueString('zaakafhandelapp', $key, $default);
			if (in_array($key, self::CREDENTIAL_KEYS, true) === false) {
				$data[$key] = $value;
				continue;
			}

			$data[$key] = '';
			if ($value !== '') {
				$data[$key] = '***';
			}
		}

		return new JSONResponse($data);
	}//end index()

	/**
	 * Persist (upsert) configuration values supplied by an admin.
	 *
	 * Only keys present in WRITABLE_KEYS are accepted; all others are ignored.
	 * Credential values are redacted in the response. Admin-only: @NoAdminRequired omitted.
	 *
	 * The method is named "save" rather than "create" because it behaves as an upsert —
	 * it creates or updates configuration keys in a single idempotent POST (L3).
	 *
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse The persisted keys with credential values redacted.
	 *
	 * @spec openspec/specs/app-configuration/spec.md#REQ-001
	 */
	public function save(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$requestData = $this->request->getParams();

		$data = [];
		foreach (self::WRITABLE_KEYS as $key) {
			if (array_key_exists($key, $requestData) === false) {
				continue;
			}

			$this->config->setValueString('zaakafhandelapp', $key, (string)$requestData[$key]);
			if (in_array($key, self::CREDENTIAL_KEYS, true) === true) {
				$data[$key] = '***';
				continue;
			}

			$data[$key] = $this->config->getValueString('zaakafhandelapp', $key);
		}

		return new JSONResponse($data);
	}//end save()
}//end class
