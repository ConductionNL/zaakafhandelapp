<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;

/**
 * Controller for handling employees (medewerkers) operations
 */
class MedewerkersController extends Controller
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
	 * Return (and search) all employees
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

		 // Fetch employees based on filters and order
		 $data = $this->objectService->getResultArrayForRequest('medewerkers', $requestParams);
 
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
	 * Read a single employee
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function show(string $id): JSONResponse
	{
        // Fetch the employee by its ID
        $object = $this->objectService->getObject('medewerkers', $id);

        // Return the employee as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Create an employee
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

        // Remove the 'id' field if it exists, as we're creating a new employee
        unset($data['id']);

        // Save the new employee
        $object = $this->objectService->saveObject('medewerkers', $data);
        
        // Return the created employee as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Update an employee
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

        // Save the updated employee
        $object = $this->objectService->saveObject('medewerkers', $data);
        
        // Return the updated employee as a JSON response
        return new JSONResponse($object);
	}

	/**
	 * Delete an employee
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 */
	public function destroy(string $id): JSONResponse
	{
        // Delete the employee
        $result = $this->objectService->deleteObject('medewerkers', $id);

        // Return the result as a JSON response
		return new JSONResponse(['success' => $result], $result === true ? '200' : '404');
	}

	/**
     * Get audit trail for a specific employee
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        $auditTrail = $this->objectService->getAuditTrail('medewerkers', $id);
        return new JSONResponse($auditTrail);
    }
}
