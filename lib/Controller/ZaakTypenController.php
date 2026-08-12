<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

/**
 * Controller for zaaktypen (case types) operations.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZaakTypenController extends Controller {
	/**
	 * ZaakTypenController constructor.
	 *
	 * @param string $appName The application name
	 * @param IRequest $request The request object
	 * @param ObjectService $objectService Open Register object access for the zaaktypen schema
	 * @param IUserSession $userSession The user session used to reject anonymous callers
	 * @param LoggerInterface $logger Logger for failed read/write operations
	 */
	public function __construct(
		$appName,
		IRequest $request,
		private readonly ObjectService $objectService,
		private readonly IUserSession $userSession,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct(appName: $appName, request: $request);
	}//end __construct()

	/**
	 * Return (and serach) all objects
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
	 */
	public function index(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		// Retrieve all request parameters
		$requestParams = $this->request->getParams();

		// Fetch catalog objects based on filters and order
		$data = $this->objectService->getResultArrayForRequest('zaaktypen', $requestParams);

		// Return JSON response
		return new JSONResponse($data);
	}//end index()

	/**
	 * Render no page.
	 *
	 * @param string|null $getParameter Optional GET parameter
	 * @return TemplateResponse The rendered template response
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-003
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $getParameter is an NC route param
	 *   reserved for future SPA deep-linking; the PHP layer renders a shell template only.
	 */
	public function page(?string $getParameter): TemplateResponse {
		try {
			// Create a new TemplateResponse for the index page
			$response = new TemplateResponse(
				$this->appName,
				'index',
				[]
			);

			// Set up Content Security Policy
			$csp = new ContentSecurityPolicy();
			$csp->addAllowedConnectDomain('*');
			$response->setContentSecurityPolicy($csp);

			return $response;
		} catch (\Exception $e) {
			// Return an error template response if an exception occurs
			return new TemplateResponse(
				$this->appName,
				'error',
				['error' => $e->getMessage()],
				TemplateResponse::RENDER_AS_ERROR,
				Http::STATUS_INTERNAL_SERVER_ERROR
			);
		}//end try
	}//end page()

	/**
	 * Read a single object
	 *
	 * @param string $id The zaaktype ID
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
	 */
	public function show(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Fetch the catalog object by its ID
			$object = $this->objectService->getObject('zaaktypen', $id);

			// Return the catalog as a JSON response
			return new JSONResponse($object);
		} catch (DoesNotExistException $e) {
			return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to read zaaktype: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not read zaaktype'], Http::STATUS_INTERNAL_SERVER_ERROR);
		}//end try
	}//end show()

	/**
	 * Create an object. Admin-only: zaaktypen are validation master data.
	 *
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 */
	public function create(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Get all parameters from the request
			$data = $this->request->getParams();

			// Remove the 'id' field if it exists, as we're creating a new object
			unset($data['id']);

			// Save the new catalog object
			$object = $this->objectService->saveObject('zaaktypen', $data);

			// Return the created object as a JSON response
			return new JSONResponse($object);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to create zaaktype: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not create zaaktype'], Http::STATUS_BAD_REQUEST);
		}//end try
	}//end create()

	/**
	 * Update an object. Admin-only: zaaktypen are validation master data.
	 *
	 * @param string $id The zaaktype ID
	 *
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $id is part of the NC route signature;
	 *   the full payload is consumed via $this->request->getParams() instead.
	 */
	public function update(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Get all parameters from the request
			$data = $this->request->getParams();

			// Remove the 'id' field if it exists, as we're creating a new object
			unset($data['id']);

			$data['id'] = $id;

			// Save the new catalog object
			$object = $this->objectService->saveObject('zaaktypen', $data);

			// Return the created object as a JSON response
			return new JSONResponse($object);
		} catch (DoesNotExistException $e) {
			return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to update zaaktype: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not update zaaktype'], Http::STATUS_BAD_REQUEST);
		}//end try
	}//end update()

	/**
	 * Delete an object. Admin-only: zaaktypen are validation master data.
	 *
	 * @param string $id The zaaktype ID
	 *
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
	 */
	public function destroy(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Delete the catalog object
			$result = $this->objectService->deleteObject('zaaktypen', $id);

			if ($result === true) {
				$status = Http::STATUS_OK;
			} else {
				$status = Http::STATUS_NOT_FOUND;
			}

			// Return the result as a JSON response
			return new JSONResponse(['success' => $result], $status);
		} catch (DoesNotExistException $e) {
			return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to delete zaaktype: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not delete zaaktype'], Http::STATUS_INTERNAL_SERVER_ERROR);
		}//end try
	}//end destroy()
}//end class
