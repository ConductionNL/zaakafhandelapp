<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Exception;

/**
 * Controller class for handling object-related operations
 */
class ObjectsController extends Controller
{
    public function __construct(
		$appName,
		IRequest $request,
        private readonly ObjectService $objectService
	)
    {
        parent::__construct($appName, $request);
    }

    /**
     * Validates if an object type is present in request and properly configured
     *
     * @param array $requestParams Request parameters to check
     * @throws Exception If object type is missing or not properly configured
     * @return string The validated object type
     */
    private function validateObjectType(array $requestParams): string {
        // Check if objectType is provided in request parameters
        if (!isset($requestParams['_objectType'])) {
            throw new Exception('Missing required parameter _objectType');
        }

        $objectType = $requestParams['_objectType'];

        try {
            // This will throw an exception if object type is not properly configured
            $this->objectService->getMapper($objectType);
        } catch (Exception $e) {
            throw $e;
        }

        return $objectType;
    }

	/**
	 * Return (and search) all objects
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function index(): JSONResponse
	{
		// Retrieve all request parameters
		$requestParams = $this->request->getParams();

        try {
            // Validate object type configuration
            $objectType = $this->validateObjectType($requestParams);

			unset($requestParams['_objectType']);

            // Fetch objects based on filters and order using provided objectType
            $data = $this->objectService->getResultArrayForRequest(
                objectType: $objectType,
                requestParams: $requestParams
            );

            // Return JSON response
            return new JSONResponse($data);
        } catch (Exception $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                400
            );
        }
	}

	/**
	 * Read a single object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function show(string $id): JSONResponse
	{
        try {
            // Get request parameters and validate object type
            $requestParams = $this->request->getParams();
            $objectType = $this->validateObjectType($requestParams);

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
        }
	}

	/**
	 * Create an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function create(): JSONResponse
	{
        try {
            // Get all parameters from the request
            $data = $this->request->getParams();
            $objectType = $this->validateObjectType($data);

            // Remove the 'id' field if it exists, as we're creating a new object
            unset($data['id']);
            unset($data['_objectType']);
            unset($data['_route']);

            // Save the new object
            $object = $this->objectService->saveObject($objectType, $data);

            // Return the created object as a JSON response
            return new JSONResponse($object);
        } catch (Exception $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                400
            );
        }
	}

	/**
	 * Update an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function update(string $id): JSONResponse
	{
        try {
            // Get all parameters from the request
            $data = $this->request->getParams();
            $objectType = $this->validateObjectType($data);

            // Ensure ID in data matches URL parameter
            $data['id'] = $id;
            unset($data['_objectType']);
			unset($data['_route']);

            // Save the updated object
            $object = $this->objectService->saveObject($objectType, $data);

            // Return the updated object as a JSON response
            return new JSONResponse($object);
        } catch (Exception $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                400
            );
        }
	}

	/**
	 * Delete an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function destroy(string $id): JSONResponse
	{
        try {
            // Get request parameters and validate object type
            $requestParams = $this->request->getParams();
            $objectType = $this->validateObjectType($requestParams);

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
	}

	/**
     * Get audit trail for a specific object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        try {
            // Get request parameters and validate object type
            $requestParams = $this->request->getParams();
            $objectType = $this->validateObjectType($requestParams);

            $auditTrail = $this->objectService->getAuditTrail($objectType, $id);
            return new JSONResponse($auditTrail);
        } catch (Exception $e) {
            return new JSONResponse(
                ['error' => $e->getMessage()],
                400
            );
        }
    }
}
