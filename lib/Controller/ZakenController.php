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
 * Geeft invulling aan https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZakenController extends Controller {
	/**
	 * Constructor for ZakenController.
	 *
	 * @param string $appName The name of the app
	 * @param IRequest $request The request object
	 * @param ObjectService $objectService Service for reading and writing zaak objects
	 * @param IUserSession $userSession The current user session
	 * @param LoggerInterface $logger Logger for failed zaak operations
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
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-001
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function index(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		// Retrieve all request parameters
		$requestParams = $this->request->getParams();

		// Fetch catalog objects based on filters and order
		$data = $this->objectService->getResultArrayForRequest('zaken', $requestParams);

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
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
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
	 * @param string $id The identifier of the zaak to read
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-002
	 */
	public function show(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Fetch the catalog object by its ID
			$object = $this->objectService->getObject('zaken', $id);

			if ($object === null) {
				return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
			}

			// Return the catalog as a JSON response
			return new JSONResponse($object);
		} catch (DoesNotExistException $e) {
			return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to read zaak: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not read zaak'], Http::STATUS_INTERNAL_SERVER_ERROR);
		}//end try
	}//end show()

	/**
	 * Creatue an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
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

			// Strip system-managed ZGW fields that must be set server-side (ZGW API-principes).
			unset(
				$data['bronorganisatie'],
				$data['verantwoordelijkeOrganisatie'],
				$data['identificatie'],
				$data['archiefstatus'],
				$data['created'],
				$data['updated']
			);

			// Default archiefstatus to 'nog_te_archiveren' for new zaken so that
			// ZGWZaakValidationService::checkArchivePrerequisites passes on deployments
			// whose schema does not define this default (C2 fix).
			$data['archiefstatus'] = 'nog_te_archiveren';

			// Save the new catalog object
			$object = $this->objectService->saveObject('zaken', $data);

			// Return the created object as a JSON response
			return new JSONResponse($object);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to create zaak: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not create zaak'], Http::STATUS_BAD_REQUEST);
		}//end try
	}//end create()

	/**
	 * Update an object
	 *
	 * @param string $id The identifier of the zaak to update
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
	 */
	public function update(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Get all parameters from the request
			$data = $this->request->getParams();

			// Pin the ID from the URL to prevent IDOR: body-supplied id must not override path id.
			$data['id'] = $id;

			// Strip system-managed ZGW fields that must not be overwritten via the request body.
			unset(
				$data['bronorganisatie'],
				$data['verantwoordelijkeOrganisatie'],
				$data['identificatie'],
				$data['archiefstatus'],
				$data['created'],
				$data['updated']
			);

			// Save the updated object
			$object = $this->objectService->saveObject('zaken', $data);

			// Return the created object as a JSON response
			return new JSONResponse($object);
		} catch (DoesNotExistException $e) {
			return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to update zaak: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not update zaak'], Http::STATUS_BAD_REQUEST);
		}//end try
	}//end update()

	/**
	 * Delate an object
	 *
	 * @param string $id The identifier of the zaak to delete
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
	 */
	public function destroy(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Delete the catalog object
			$result = $this->objectService->deleteObject('zaken', $id);

			if ($result === true) {
				$statusCode = Http::STATUS_OK;
			} else {
				$statusCode = Http::STATUS_NOT_FOUND;
			}

			// Return the result as a JSON response
			return new JSONResponse(['success' => $result], $statusCode);
		} catch (DoesNotExistException $e) {
			return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to delete zaak: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not delete zaak'], Http::STATUS_INTERNAL_SERVER_ERROR);
		}//end try
	}//end destroy()

	/**
	 * Get audit trail for a specific zaak
	 *
	 * @param string $id The identifier of the zaak whose audit trail is returned
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-004
	 */
	public function getAuditTrail(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Scope guard — NOT an authorisation guard. It confirms the id resolves
			// inside the register/schema configured for 'zaken'; it does NOT
			// establish that this caller may read this zaak. OpenRegister's RBAC
			// returns true for a schema with an empty `authorization` block and this
			// app ships none (ConductionNL/.github#372), so getObject() bottoms out
			// in $mapper->find($id) with no caller identity. Per-object
			// authorisation is still missing — see zaakafhandelapp#347.
			$object = $this->objectService->getObject('zaken', $id);
			if ($object === null) {
				return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
			}

			$auditTrail = $this->objectService->getAuditTrail($id);
			return new JSONResponse($auditTrail);
		} catch (DoesNotExistException $e) {
			return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to read zaak audit trail: ' . $e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
			return new JSONResponse(['error' => 'Could not read audit trail'], Http::STATUS_INTERNAL_SERVER_ERROR);
		}//end try
	}//end getAuditTrail()
}//end class
