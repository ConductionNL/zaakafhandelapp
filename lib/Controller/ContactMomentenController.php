<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;

/**
 * Controller for handling contact moments (contactmomenten) operations
 */
class ContactMomentenController extends Controller
{
    public function __construct(
		$appName,
		IRequest $request,
        private readonly ObjectService $objectService,
	)
    {
        parent::__construct($appName, $request);
    }

	/**
	 * Return (and search) all contact moments
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

		 // Fetch contact moments based on filters and order
		 $data = $this->objectService->getResultArrayForRequest('contactmomenten', $requestParams);
 
		 // Return JSON response
		 return new JSONResponse($data);
	}

    /**
     * Render no page.
     *
     * @param string|null $getParameter Optional GET parameter
     * @return TemplateResponse The rendered template response
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function page(?string $getParameter): TemplateResponse
    {
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
                '500'
            );
        }
    }

	/**
	 * Read a single contact moment
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function show(string $id): JSONResponse
	{
        // Fetch the contact moment by its ID
        $object = $this->objectService->getObject('contactmomenten', $id);

        // Return the contact moment as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Create a contact moment
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function create(): JSONResponse
	{
        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new contact moment
        unset($data['id']);

        // Save the new contact moment
        $object = $this->objectService->saveObject('contactmomenten', $data);
        
        // Return the created contact moment as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Update a contact moment
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function update(string $id): JSONResponse
	{
        // Get all parameters from the request
        $data = $this->request->getParams();

        // Save the updated contact moment
        $object = $this->objectService->saveObject('contactmomenten', $data);
        
        // Return the updated contact moment as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Delete a contact moment
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function destroy(string $id): JSONResponse
	{
        // Delete the contact moment
        $result = $this->objectService->deleteObject('contactmomenten', $id);

        // Return the result as a JSON response
		return new JSONResponse(['success' => $result], $result === true ? '200' : '404');
	}

	/**
     * Get audit trail for a specific contact moment
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        $auditTrail = $this->objectService->getAuditTrail('contactmomenten', $id);
        return new JSONResponse($auditTrail);
    }
}
