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
class ZaakObjectenController extends Controller {
	/**
	 * Build the zaakobjecten controller.
	 *
	 * @param string $appName The application name.
	 * @param IRequest $request The current HTTP request.
	 * @param IAppConfig $config The app configuration store.
	 * @param IUserSession $userSession Session used to resolve the acting user.
	 */
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IAppConfig $config,
		private readonly IUserSession $userSession,
	) {
		parent::__construct(appName: $appName, request: $request);
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
	 * @param CallService $callService Service used to call the ZRC source.
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
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
	 * @param string $id The identifier of the zaakobject to read.
	 * @param CallService $callService Service used to call the ZRC source.
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
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
	 * @param CallService $callService Service used to call the ZRC source.
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 */
	public function create(CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		// Get post from requests
		$body = $this->request->getParams();
		$results = $callService->create(source: 'zrc', endpoint: 'zaakobjecten', data: $body);
		return new JSONResponse($results);
	}//end create()

	/**
	 * Update an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param string $id The identifier of the zaakobject to update.
	 * @param CallService $callService Service used to call the ZRC source.
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
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
	 * @param string $id The identifier of the zaakobject to delete.
	 * @param CallService $callService Service used to call the ZRC source.
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
	 */
	public function destroy(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$callService->destroy(source: 'zrc', endpoint: 'zaakobjecten', id: $id);

		return new JSONResponse([]);
	}//end destroy()
}//end class
