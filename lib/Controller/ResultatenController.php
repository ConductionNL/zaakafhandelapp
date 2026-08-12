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
class ResultatenController extends Controller {
	/**
	 * Constructor for ResultatenController.
	 *
	 * @param string $appName The name of the app
	 * @param IRequest $request The request object
	 * @param IAppConfig $config The app configuration
	 * @param IUserSession $userSession The current user session
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
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-003
	 */
	public function pages(): TemplateResponse {
		return new TemplateResponse(
			'zaakafhandelapp',
			'index',
			[]
		);
	}//end pages()

	/**
	 * Return (and serach) all objects
	 *
	 * @param CallService $callService Service used to call the ZRC source
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
	 */
	public function index(CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$results = $callService->index(source: 'zrc', endpoint: 'resultaten');
		return new JSONResponse($results);
	}//end index()

	/**
	 * Read a single object
	 *
	 * @param string $id The identifier of the resultaat to read
	 * @param CallService $callService Service used to call the ZRC source
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
	 */
	public function show(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$results = $callService->show(source: 'zrc', endpoint: 'resultaten', id: $id);
		return new JSONResponse($results);
	}//end show()

	/**
	 * Creatue an object
	 *
	 * @param CallService $callService Service used to call the ZRC source
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 */
	public function create(CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		// Get post from requests
		$body = $this->request->getParams();
		$results = $callService->create(source: 'zrc', endpoint: 'resultaten', data: $body);
		return new JSONResponse($results);
	}//end create()

	/**
	 * Update an object
	 *
	 * @param string $id The identifier of the resultaat to update
	 * @param CallService $callService Service used to call the ZRC source
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 */
	public function update(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$body = $this->request->getParams();
		$results = $callService->update(source: 'zrc', endpoint: 'resultaten', data: $body, id: $id);
		return new JSONResponse($results);
	}//end update()

	/**
	 * Delate an object
	 *
	 * @param string $id The identifier of the resultaat to delete
	 * @param CallService $callService Service used to call the ZRC source
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 */
	public function destroy(string $id, CallService $callService): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$callService->destroy(source: 'zrc', endpoint: 'resultaten', id: $id);

		return new JSONResponse([]);
	}//end destroy()
}//end class
