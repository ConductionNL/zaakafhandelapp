<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

/**
 * Controller for klanten master data.
 *
 * Klanten records are used as contact targets in TakenController mail
 * notifications and throughout the zaak lifecycle. Mutations require admin
 * privileges to prevent any authenticated user from injecting arbitrary
 * klant records (see issue #269).
 */
class KlantenController extends Controller
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
        $data = $this->objectService->getResultArrayForRequest('klanten', $requestParams);

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
        $object = $this->objectService->getObject('klanten', $id);

        // Return the catalog as a JSON response
        return new JSONResponse($object);
    }//end show()

    /**
     * Create an object. Admin-only: klanten are master data.
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
        $object = $this->objectService->saveObject('klanten', $data);

        // Return the created object as a JSON response
        return new JSONResponse($object);
    }//end create()

    /**
     * Update an object. Admin-only: klanten are master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function update(string $id): JSONResponse
    {
        // Get all parameters from the request
        $data = $this->request->getParams();

        // Save the updated catalog object
        $object = $this->objectService->saveObject('klanten', $data);

        // Return the updated object as a JSON response
        return new JSONResponse($object);
    }//end update()

    /**
     * Delete an object. Admin-only: klanten are master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function destroy(string $id): JSONResponse
    {
        // Delete the catalog object
        $result = $this->objectService->deleteObject('klanten', $id);

        // Return the result as a JSON response
        return new JSONResponse(['success' => $result], $result === true ? 200 : 404);
    }//end destroy()

    /**
     * Get zaken for a specific klant.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function getZaken(string $id): JSONResponse
    {
        $requestParams = ['klant' => $id];
        $zaken         = $this->objectService->getResultArrayForRequest('zaken', $requestParams);
        return new JSONResponse($zaken);
    }//end getZaken()

    /**
     * Get taken for a specific klant.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function getTaken(string $id): JSONResponse
    {
        $requestParams = ['klant' => $id];
        $taken         = $this->objectService->getResultArrayForRequest('taken', $requestParams);
        return new JSONResponse($taken);
    }//end getTaken()

    /**
     * Get berichten for a specific klant.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function getBerichten(string $id): JSONResponse
    {
        $requestParams = ['gebruikerID' => $id];
        $berichten     = $this->objectService->getResultArrayForRequest('klanten', $requestParams);
        return new JSONResponse($berichten);
    }//end getBerichten()

    /**
     * Get contactmomenten for a specific klant.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function getContactmomenten(string $id): JSONResponse
    {
        $requestParams   = ['klant' => $id];
        $contactmomenten = $this->objectService->getResultArrayForRequest('contactmomenten', $requestParams);
        return new JSONResponse($contactmomenten);
    }//end getContactmomenten()

    /**
     * Get audit trail for a specific klant.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        $auditTrail = $this->objectService->getAuditTrail('klanten', $id);
        return new JSONResponse($auditTrail);
    }//end getAuditTrail()
}//end class
