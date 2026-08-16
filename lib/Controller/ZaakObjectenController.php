<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Controller for zaakobjecten (case objects) resources.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
/**
 * Every routed method here carries the gate-7 exemption tag for the
 * reason set out in full in `StatusenController`'s class docblock: this
 * controller owns no storage, each method is a thin outbound proxy to the
 * external ZRC configured in app settings, and `CallService` authenticates with
 * ONE instance-wide credential from `IAppConfig`. There is no
 * zaakafhandelapp-owned object to scope to the caller.
 *
 * The exemption claims no per-user scoping, because there is none. ADR-085
 * places this surface in openconnector — zaakafhandelapp#381 records the debt.
 */
class ZaakObjectenController extends Controller {
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IAppConfig $config,
		private readonly IUserSession $userSession,
	) {
		parent::__construct($appName, $request);
	}//end __construct()

	/**
	 * This returns the template of the main app's page
	 * It adds some data to the template (app version)
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 */
	public function page(): TemplateResponse {
		return new TemplateResponse(
			'zaakafhandelapp',
			'index',
			[]
		);
	}//end page()

	/**
	 * Return (and serach) all objects
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZGW source under one
	 *   instance-wide credential; no zaakafhandelapp-owned object exists to scope.
	 */
	public function index(CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$results = $callService->index(source: 'zrc', endpoint: 'zaakobjecten');
		return new JSONResponse($results);
	}//end index()

	/**
	 * Read a single object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZGW source under one
	 *   instance-wide credential; no zaakafhandelapp-owned object exists to scope.
	 */
	public function show(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$results = $callService->show(source: 'zrc', endpoint: 'zaakobjecten', id: $id);
		return new JSONResponse($results);
	}//end show()

	/**
	 * Creatue an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZGW source under one
	 *   instance-wide credential; no zaakafhandelapp-owned object exists to scope.
	 */
	public function create(CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		// get post from requests
		$body = $this->request->getParams();
		$results = $callService->create(source: 'zrc', endpoint: 'zaakobjecten', data: $body);
		return new JSONResponse($results);
	}//end create()

	/**
	 * Update an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZGW source under one
	 *   instance-wide credential; no zaakafhandelapp-owned object exists to scope.
	 */
	public function update(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$body = $this->request->getParams();
		$results = $callService->update(source: 'zrc', endpoint: 'zaakobjecten', data: $body, id: $id);
		return new JSONResponse($results);
	}//end update()

	/**
	 * Delate an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZGW source under one
	 *   instance-wide credential; no zaakafhandelapp-owned object exists to scope.
	 */
	public function destroy(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$callService->destroy(source: 'zrc', endpoint: 'zaakobjecten', id: $id);

		return new JSONResponse([]);
	}//end destroy()
}//end class
