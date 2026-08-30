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
 * Geeft invulling aan https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
/**
 * ## Why every routed method here carries the gate-7 exemption tag
 *
 * This controller owns no storage. Each method is a thin outbound proxy to the
 * external ZRC configured in app settings, reached through `CallService`, which
 * authenticates with ONE instance-wide credential read from `IAppConfig`
 * (`zrcClientId` / `zrcSecret` / `zrcKey` — see `CallService::getAuthorization`).
 *
 * So there is no zaakafhandelapp-owned object to scope to the caller, and the
 * per-object authorisation boundary is the external ZRC's own, applied to that
 * one credential. What the exemption does NOT claim — and what a reviewer
 * should not read into it — is per-user scoping: there is none here. Every user
 * the app is enabled for reaches the same external register with the same
 * rights. That is the deployment model (one instance serves one gemeente, and
 * its case handlers share the caseload), not an oversight, but it IS a coarse
 * boundary and it is recorded as such.
 *
 * The `if (getUser() === null) → 401` preamble in each method is
 * AUTHENTICATION, not authorisation, and the gate is right to ignore it
 * (.github#365). It is kept because an anonymous call should not consume the
 * instance's ZRC credential.
 *
 * ADR-085 places this whole proxy surface in openconnector rather than in a
 * leaf app — see zaakafhandelapp#381. These exemptions are the interim record
 * of that debt, not a clean bill.
 */
class StatusenController extends Controller {
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
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-003
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
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZRC under one instance-wide
	 *   credential; no zaakafhandelapp-owned object exists to scope. See the class docblock.
	 */
	public function index(CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$results = $callService->index(source: 'zrc', endpoint: 'statussen');
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
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZRC under one instance-wide
	 *   credential; no zaakafhandelapp-owned object exists to scope. See the class docblock.
	 */
	public function show(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$results = $callService->show(source: 'zrc', endpoint: 'statussen', id: $id);
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
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZRC under one instance-wide
	 *   credential; no zaakafhandelapp-owned object exists to scope. See the class docblock.
	 */
	public function create(CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		// get post from requests
		$body = $this->request->getParams();
		$results = $callService->create(source: 'zrc', endpoint: 'statussen', data: $body);
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
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZRC under one instance-wide
	 *   credential; no zaakafhandelapp-owned object exists to scope. See the class docblock.
	 */
	public function update(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$body = $this->request->getParams();
		$results = $callService->update(source: 'zrc', endpoint: 'statussen', data: $body, id: $id);
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
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 *
	 * @no-admin-idor-exempt Outbound proxy to the external ZRC under one instance-wide
	 *   credential; no zaakafhandelapp-owned object exists to scope. See the class docblock.
	 */
	public function destroy(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$callService->destroy(source: 'zrc', endpoint: 'statussen', id: $id);

		return new JSONResponse([]);
	}//end destroy()
}//end class
