<?php

namespace OCA\ZaakAfhandelApp\Controller;

use GuzzleHttp\Client;
use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\IRequest;

class TakenController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
    ) {
        parent::__construct($appName, $request);
    }


	/**
	 * Return (and serach) all objects
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function index(CallService $klantenService): JSONResponse
	{
		 // Retrieve all request parameters
		 $requestParams = $this->request->getParams();

		 // Fetch catalog objects based on filters and order
		 $data = $this->objectService->getResultArrayForRequest('taken', $requestParams);
 
		 // Return JSON response
		 return new JSONResponse($data);
	}

	/**
	 * Read a single object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function show(string $id, CallService $klantenService): JSONResponse
	{
        // Fetch the catalog object by its ID
        $object = $this->objectService->getObject('taken', $id);

        // Return the catalog as a JSON response
        return new JSONResponse($object);
	}


	/**
	 * Creatue an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function create(CallService $callService): JSONResponse
	{
        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new object
        unset($data['id']);

        // Save the new catalog object
        $object = $this->objectService->saveObject('taken', $data);
        
        // Return the created object as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Update an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function update(string $id, CallService $callService): JSONResponse
	{
        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new object
        unset($data['id']);

        // Save the new catalog object
        $object = $this->objectService->saveObject('taken', $data);
        
        // Return the created object as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Delate an object
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function destroy(string $id, CallService $callService): JSONResponse
	{
        // Delete the catalog object
        $result = $this->objectService->deleteObject('taken', $id);

        // Return the result as a JSON response
		return new JSONResponse(['success' => $result], $result === true ? '200' : '404');
	}
}
