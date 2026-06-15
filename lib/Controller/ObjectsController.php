<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;
use Exception;

/**
 * Controller class for handling object-related operations
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ObjectsController extends Controller
{
    /**
     * Fields that are system-managed per object type.
     * These must be stripped from create/update payloads to prevent mass-assignment
     * of values that are set exclusively by business logic or the platform itself.
     *
     * @var array<string,string[]>
     */
    private const SYSTEM_MANAGED_FIELDS = [
        'zaken'      => ['bronorganisatie', 'verantwoordelijkeOrganisatie', 'identificatie', 'archiefstatus', 'created', 'updated'],
        'klanten'    => ['created', 'updated'],
        'berichten'  => ['created', 'updated'],
        'taken'      => ['created', 'updated'],
        'resultaten' => ['created', 'updated'],
        'statusen'   => ['created', 'updated'],
        'besluiten'  => ['created', 'updated'],
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

    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Validate that the requested objectType is in the known allow-list.
     *
     * @param string $objectType The object type from the URL.
     *
     * @return JSONResponse|null Returns a 400 response on invalid type, null when valid.
     */
    private function validateObjectType(string $objectType): ?JSONResponse
    {
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
     * @param array  $data       The raw request payload.
     *
     * @return array The payload with system-managed fields removed.
     */
    private function stripSystemManagedFields(string $objectType, array $data): array
    {
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
    public function index(string $objectType): JSONResponse
    {
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
    public function show(string $objectType, string $id): JSONResponse
    {
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

            // Fetch the object by its ID
            $object = $this->objectService->getObject($objectType, $id, $extend);

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
    public function create(string $objectType): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $typeError = $this->validateObjectType($objectType);
        if ($typeError !== null) {
            return $typeError;
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
    public function update(string $objectType, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $typeError = $this->validateObjectType($objectType);
        if ($typeError !== null) {
            return $typeError;
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
    public function destroy(string $objectType, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $typeError = $this->validateObjectType($objectType);
        if ($typeError !== null) {
            return $typeError;
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
    public function getAuditTrail(string $objectType, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $typeError = $this->validateObjectType($objectType);
        if ($typeError !== null) {
            return $typeError;
        }

        try {
            // IDOR guard: verify the object exists (and that the current user has read access
            // via OR's RBAC) before returning its audit trail. A null return means the object
            // does not exist or is not accessible to the caller.
            $object = $this->objectService->getObject($objectType, $id);
            if ($object === null) {
                return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
            }

            $auditTrail = $this->objectService->getAuditTrail($objectType, $id);
            return new JSONResponse($auditTrail);
        } catch (Exception $e) {
            return new JSONResponse(
             ['error' => $e->getMessage()],
             400
            );
        }
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
    public function getRelations(string $objectType, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $typeError = $this->validateObjectType($objectType);
        if ($typeError !== null) {
            return $typeError;
        }

        try {
            // Fetch the object by its ID
            $relations = $this->objectService->getRelations($objectType, $id);

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
    public function getUses(string $objectType, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $typeError = $this->validateObjectType($objectType);
        if ($typeError !== null) {
            return $typeError;
        }

        $uses = $this->objectService->getUses($objectType, $id);
        return new JSONResponse($uses);
    }//end getUses()
}//end class
