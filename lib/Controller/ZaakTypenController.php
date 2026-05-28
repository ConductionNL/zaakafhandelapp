<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

/**
 * Controller for ZTC zaaktypen master data.
 *
 * Zaaktypen define the validation source-of-truth for statustypen,
 * resultaattypen, roltypen, and besluittypen.  Mutations therefore require
 * admin privileges to prevent any authenticated user from subverting zaak
 * lifecycle validation (see issue #269).
 */
class ZaakTypenController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Return (and search) all objects.
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

        // Fetch catalog objects based on filters and order
        $data = $this->objectService->getResultArrayForRequest('zaaktypen', $requestParams);

        // Return JSON response
        return new JSONResponse($data);
    }//end index()

    /**
     * Read a single object.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function show(string $id): JSONResponse
    {
        // Fetch the catalog object by its ID
        $object = $this->objectService->getObject('zaaktypen', $id);

        // Return the catalog as a JSON response
        return new JSONResponse($object);
    }//end show()

    /**
     * Create an object. Admin-only: zaaktypen are validation master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function create(): JSONResponse
    {
        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new object
        unset($data['id']);

        // Save the new catalog object
        $object = $this->objectService->saveObject('zaaktypen', $data);

        // Return the created object as a JSON response
        return new JSONResponse($object);
    }//end create()

    /**
     * Update an object. Admin-only: zaaktypen are validation master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function update(string $id): JSONResponse
    {
        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're updating
        unset($data['id']);

        // Save the updated catalog object
        $object = $this->objectService->saveObject('zaaktypen', $data);

        // Return the updated object as a JSON response
        return new JSONResponse($object);
    }//end update()

    /**
     * Delete an object. Admin-only: zaaktypen are validation master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function destroy(string $id): JSONResponse
    {
        // Delete the catalog object
        $result = $this->objectService->deleteObject('zaaktypen', $id);

        // Return the result as a JSON response
        return new JSONResponse(['success' => $result], $result === true ? 200 : 404);
    }//end destroy()
}//end class
