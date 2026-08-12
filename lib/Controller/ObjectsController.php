<?php

namespace OCA\ZaakAfhandelApp\Controller;

use Exception;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Controller class for handling object-related operations
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ObjectsController extends Controller {
	/**
	 * Fields that are system-managed per object type.
	 * These must be stripped from create/update payloads to prevent mass-assignment
	 * of values that are set exclusively by business logic or the platform itself.
	 *
	 * @var array<string,string[]>
	 */
	private const SYSTEM_MANAGED_FIELDS = [
		'zaken' => ['bronorganisatie', 'verantwoordelijkeOrganisatie', 'identificatie', 'archiefstatus', 'created', 'updated'],
		'klanten' => ['created', 'updated'],
		'berichten' => ['created', 'updated'],
		'taken' => ['created', 'updated'],
		'resultaten' => ['created', 'updated'],
		'statusen' => ['created', 'updated'],
		'besluiten' => ['created', 'updated'],
	];

	/**
	 * Explicit allow-list of object types exposed through the generic objects endpoint.
	 * Any objectType not in this list is rejected with HTTP 400 to prevent access to
	 * unintended or internal schemas (#276 — unvalidated objectType).
	 *
	 * Keep in sync with SettingsController::OBJECT_TYPES.
	 */
	private const ALLOWED_OBJECT_TYPES = [
		'berichten',
		'besluiten',
		'documenten',
		'klanten',
		'resultaten',
		'taken',
		'informatieobjecten',
		'organisaties',
		'personen',
		'zaken',
		'rollen',
		'statusen',
		'zaakeigenschappen',
		'zaaktypen',
		'contactmomenten',
		'medewerkers',
		'producten',
	];

	/**
	 * Object types whose WRITE operations this app has already decided are
	 * admin-only, and enforces on their own controllers by omitting
	 * `@NoAdminRequired`:
	 *
	 *   - `klanten`   — KlantenController::create/update/destroy,
	 *                   "Admin-only: klanten are master data."
	 *   - `zaaktypen` — ZaakTypenController::create/update/destroy,
	 *                   "Admin-only: zaaktypen are validation master data."
	 *
	 * This generic route is a SECOND path to the same writes, and it is
	 * `@NoAdminRequired`. Measured on a live instance with one non-admin
	 * account, same operation, two routes:
	 *
	 *   DELETE api/klanten/{id}          -> HTTP 403
	 *   DELETE api/objects/klanten/{id}  -> HTTP 200 {"success":true}   (deleted)
	 *   PUT    api/ztc/zaaktypen/{id}    -> HTTP 403
	 *   PUT    api/objects/zaaktypen/{id}-> HTTP 200                    (tamper persisted)
	 *
	 * So the decision was already taken and merely bypassed here. Keep this list
	 * in sync with the controllers named above.
	 *
	 * @var string[]
	 */
	private const ADMIN_ONLY_WRITE_TYPES = [
		'klanten',
		'zaaktypen',
	];

	public function __construct(
		$appName,
		IRequest $request,
		private readonly ObjectService $objectService,
		private readonly IUserSession $userSession,
		private readonly IGroupManager $groupManager,
	) {
		parent::__construct($appName, $request);
	}//end __construct()

	/**
	 * Enforce the app's existing admin-only posture on master-data writes.
	 *
	 * @param string $objectType The object type being written.
	 *
	 * @return JSONResponse|null A 403 response when the caller may not write this
	 *                           type, null when the write may proceed.
	 */
	private function guardMasterDataWrite(string $objectType): ?JSONResponse {
		if (in_array($objectType, self::ADMIN_ONLY_WRITE_TYPES, true) === false) {
			return null;
		}

		$user = $this->userSession->getUser();
		if ($user !== null && $this->groupManager->isAdmin($user->getUID()) === true) {
			return null;
		}

		return new JSONResponse(
			['error' => "Writing '$objectType' is restricted to administrators"],
			Http::STATUS_FORBIDDEN
		);
	}//end guardMasterDataWrite()

	/**
	 * Validate that the requested objectType is in the known allow-list.
	 *
	 * @param string $objectType The object type from the URL.
	 *
	 * @return JSONResponse|null Returns a 400 response on invalid type, null when valid.
	 */
	private function validateObjectType(string $objectType): ?JSONResponse {
		if (in_array($objectType, self::ALLOWED_OBJECT_TYPES, true) === false) {
			return new JSONResponse(
				['error' => "Unknown object type: $objectType"],
				Http::STATUS_BAD_REQUEST
			);
		}

		return null;
	}//end validateObjectType()

	/**
	 * Strip system-managed fields from a write payload.
	 *
	 * Removes fields listed in SYSTEM_MANAGED_FIELDS for the given objectType so that
	 * callers cannot mass-assign values that are set exclusively by business logic or
	 * the platform itself (e.g. bronorganisatie, archiefstatus, created/updated).
	 *
	 * @param string $objectType The object type being written.
	 * @param array $data The raw request payload.
	 *
	 * @return array The payload with system-managed fields removed.
	 */
	private function stripSystemManagedFields(string $objectType, array $data): array {
		$protected = self::SYSTEM_MANAGED_FIELDS[$objectType] ?? [];
		foreach ($protected as $field) {
			unset($data[$field]);
		}

		return $data;
	}//end stripSystemManagedFields()

	/**
	 * Return (and search) all objects
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $objectType The type of object to return
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function index(string $objectType): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		// Retrieve all request parameters
		$requestParams = $this->request->getParams();

		unset($requestParams['_route']);
		unset($requestParams['objectType']);
		// Nextcloud automatically adds this from the route so we need to remove it
		// Fetch catalog objects based on filters and order
		$data = $this->objectService->getResultArrayForRequest($objectType, $requestParams);

		// Return JSON response
		return new JSONResponse($data);
	}//end index()

	/**
	 * Read a single object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function show(string $objectType, string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		try {
			// Retrieve all request parameters
			$requestParams = $this->request->getParams();

			// Get extend parameter if present
			$extend = $requestParams['extend'] ?? $requestParams['_extend'] ?? [];
			if (is_string($extend)) {
				$extend = array_map('trim', explode(',', $extend));
			}

			// Fetch the object by its ID. The mapper is bound to the register and
			// schema configured for $objectType (ObjectMapperService resolves the
			// configured slugs to numeric ids), so an id belonging to another
			// register does not resolve here and must not be answered with a body.
			$object = $this->objectService->getObject($objectType, $id, $extend);
			if ($object === null) {
				return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
			}

			// Return the object as a JSON response
			return new JSONResponse($object);
		} catch (Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				400
			);
		}//end try
	}//end show()

	/**
	 * Create an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function create(string $objectType): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		$adminError = $this->guardMasterDataWrite($objectType);
		if ($adminError !== null) {
			return $adminError;
		}

		try {
			// Get all parameters from the request
			$data = $this->request->getParams();

			// Remove the 'id' field if it exists, as we're creating a new object
			unset($data['id']);

			// Strip system-managed fields to prevent mass-assignment of platform-controlled values.
			$data = $this->stripSystemManagedFields($objectType, $data);

			// Save the new object
			$object = $this->objectService->saveObject($objectType, $data);

			// Return the created object as a JSON response
			return new JSONResponse($object);
		} catch (Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				400
			);
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
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function update(string $objectType, string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		$adminError = $this->guardMasterDataWrite($objectType);
		if ($adminError !== null) {
			return $adminError;
		}

		try {
			// Get all parameters from the request
			$data = $this->request->getParams();

			// Ensure ID in data matches URL parameter
			$data['id'] = $id;

			// Strip system-managed fields to prevent mass-assignment of platform-controlled values.
			$data = $this->stripSystemManagedFields($objectType, $data);

			// Save the updated object
			$object = $this->objectService->saveObject($objectType, $data);

			// Return the updated object as a JSON response
			return new JSONResponse($object);
		} catch (Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				400
			);
		}//end try
	}//end update()

	/**
	 * Delete an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function destroy(string $objectType, string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		$adminError = $this->guardMasterDataWrite($objectType);
		if ($adminError !== null) {
			return $adminError;
		}

		try {
			// Delete the object
			$result = $this->objectService->deleteObject($objectType, $id);

			// Return the result as a JSON response
			return new JSONResponse(['success' => $result], $result === true ? 200 : 404);
		} catch (Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				400
			);
		}
	}//end destroy()

	/**
	 * Get audit trail for a specific object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getAuditTrail(string $objectType, string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		try {
			// Scope guard — NOT an authorisation guard.
			//
			// This confirms the id resolves inside the register/schema configured
			// for $objectType, so an id from another register cannot be used to
			// pull a trail through this route. It does NOT establish that the
			// caller may see this object: OpenRegister's RBAC returns true for a
			// schema with an empty `authorization` block and this app ships none
			// (ConductionNL/.github#372). Per-object authorisation is still
			// missing here — see zaakafhandelapp#347.
			//
			// The previous comment on these lines claimed the resolve happened
			// "via OR's RBAC". It does not; getObject() bottoms out in
			// $mapper->find($id) with no caller identity of any kind.
			$object = $this->objectService->getObject($objectType, $id);
			if ($object === null) {
				return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
			}

			$auditTrail = $this->objectService->getAuditTrail($id);
			return new JSONResponse($auditTrail);
		} catch (Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				400
			);
		}//end try
	}//end getAuditTrail()

	/**
	 * Get all relations for a specific object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getRelations(string $objectType, string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		try {
			// Scope guard — NOT an authorisation guard (see ::getAuditTrail).
			// OR resolves the relation graph from the uuid alone, so without this
			// an arbitrary uuid from ANY register could be walked through the
			// route for a type it has nothing to do with.
			$object = $this->objectService->getObject($objectType, $id);
			if ($object === null) {
				return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
			}

			$relations = $this->objectService->getRelations($id);

			// Return the object as a JSON response
			return new JSONResponse($relations);
		} catch (Exception $e) {
			return new JSONResponse(
				['error' => $e->getMessage()],
				400
			);
		}
	}//end getRelations()

	/**
	 * Get all uses for a specific object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getUses(string $objectType, string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$typeError = $this->validateObjectType($objectType);
		if ($typeError !== null) {
			return $typeError;
		}

		try {
			// Scope guard — NOT an authorisation guard (see ::getAuditTrail).
			$object = $this->objectService->getObject($objectType, $id);
			if ($object === null) {
				return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
			}
		} catch (Exception $e) {
			return new JSONResponse(['error' => $e->getMessage()], 400);
		}

		$uses = $this->objectService->getUses($id);
		return new JSONResponse($uses);
	}//end getUses()
}//end class
