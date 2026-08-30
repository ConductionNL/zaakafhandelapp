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
/**
 * ## Why the routed methods here carry the gate-7 exemption tag
 *
 * THIS IS THE CANONICAL NOTE for zaakafhandelapp's OpenRegister-backed
 * controllers; the others point at it rather than repeating it.
 *
 * Per-object authorisation is delegated to OpenRegister's organisation
 * multitenancy, reached four hops down:
 *
 *     Controller -> ZaakAfhandelApp\Service\ObjectService
 *                -> ObjectQueryService
 *                -> ObjectMapperService::getMapper()
 *                -> OpenRegister\Service\ObjectService
 *
 * gate-7 cannot see it. Its Pattern 2b clears a leaf app only when the
 * controller FILE imports OR's `ObjectService`; these files import
 * zaakafhandelapp's own facade of the same name, which the pattern
 * deliberately excludes ("a local class of that name clears nothing") because
 * a local `ObjectService` is usually the app's own storage. Here it is not —
 * it is a pass-through — but the gate has no way to know that.
 *
 * ⚠️ MEASURED, NOT ASSUMED. A claim that a guard lives further down is worth
 * nothing until someone has watched it refuse. Probed on the shared dev
 * instance 2026-08-16 with two real users over HTTP:
 *
 *   * `zaa-idor-a` created a zaak; `owner=zaa-idor-a`,
 *     `organisation=286a9152-…` (Default Organisation).
 *   * `zaa-idor-b` in the SAME organisation read it: **HTTP 200, full body**,
 *     and it appeared in their list.
 *   * `zaa-idor-b` moved to Gemeente Amsterdam and read the same id:
 *     **HTTP 404**, and the object was **absent from the list** (0 results).
 *
 * So the boundary is real and it is the ORGANISATION, not the individual user
 * — and it refuses 404-style, which gate-7's own finding text names as a
 * legitimate guard ("chosen so a 403 cannot become an existence oracle").
 *
 * The exemption therefore claims exactly this and no more: cross-tenant access
 * is refused; colleagues inside one gemeente share the caseload by design.
 * A per-user restriction is available where a case needs one — OR's
 * `authorization` block on the object — and is not used here.
 *
 * An explicit `_rbac: false` anywhere in that chain would withdraw the claim.
 * There are two `_multitenancy: false` calls in the app, both in
 * `ObjectMapperService` and both on register/schema METADATA (lines 198, 233),
 * not on object data.
 */
class ZakenController extends Controller {
	public function __construct(
		$appName,
		IRequest $request,
		private readonly ObjectService $objectService,
		private readonly IUserSession $userSession,
		private readonly LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
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
	 *
	 * @no-admin-idor-exempt Per-object authorisation delegated to OpenRegister's
	 *   organisation multitenancy; cross-tenant reads measured to 404. Class docblock.
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
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-002
	 *
	 * @no-admin-idor-exempt Per-object authorisation delegated to OpenRegister's
	 *   organisation multitenancy; cross-tenant reads measured to 404. Class docblock.
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
	 *
	 * @no-admin-idor-exempt Per-object authorisation delegated to OpenRegister's
	 *   organisation multitenancy; cross-tenant reads measured to 404. Class docblock.
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
			unset($data['bronorganisatie'], $data['verantwoordelijkeOrganisatie'], $data['identificatie'], $data['archiefstatus'], $data['created'], $data['updated']);

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
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
	 *
	 * @no-admin-idor-exempt Per-object authorisation delegated to OpenRegister's
	 *   organisation multitenancy; cross-tenant reads measured to 404. Class docblock.
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
			unset($data['bronorganisatie'], $data['verantwoordelijkeOrganisatie'], $data['identificatie'], $data['archiefstatus'], $data['created'], $data['updated']);

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
	 * Delete an object
	 *
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
	 *
	 * @no-admin-idor-exempt Per-object authorisation delegated to OpenRegister's
	 *   organisation multitenancy; cross-tenant reads measured to 404. Class docblock.
	 */
	public function destroy(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		try {
			// Delete the catalog object
			$result = $this->objectService->deleteObject('zaken', $id);

			// Return the result as a JSON response
			return new JSONResponse(['success' => $result], $result === true ? Http::STATUS_OK : Http::STATUS_NOT_FOUND);
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
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-004
	 *
	 * @no-admin-idor-exempt Per-object authorisation delegated to OpenRegister's
	 *   organisation multitenancy; cross-tenant reads measured to 404. Class docblock.
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
