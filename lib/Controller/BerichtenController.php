<?php
/**
 * Controller for handling berichten operations.
 *
 * @category Controller
 * @package  OCA\ZaakAfhandelApp\Controller
 * @author   Conduction b.v. <info@conduction.nl>
 * @license  EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link     https://conduction.nl
 */

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;

/**
 * Controller for berichten (messages) CRUD operations.
 */
class BerichtenController extends Controller
{
    /**
     * Constructor for BerichtenController.
     *
     * @param string        $appName       The app name.
     * @param IRequest      $request       The request object.
     * @param ObjectService $objectService The object service.
     */
    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
    ) {
        parent::__construct(appName: $appName, request: $request);
    }//end __construct()

    /**
     * Return (and search) all berichten.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function index(): JSONResponse
    {
         // Retrieve all request parameters.
         $requestParams = $this->request->getParams();

         // Fetch objects based on filters and order.
         $data = $this->objectService->getResultArrayForRequest('berichten', $requestParams);

         // Return JSON response.
         return new JSONResponse($data);
    }//end index()

    /**
     * Render no page.
     *
     * @param string|null $getParameter Optional GET parameter.
     *
     * @return TemplateResponse The rendered template response.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function page(?string $getParameter): TemplateResponse
    {
        try {
            // Create a new TemplateResponse for the index page.
            $response = new TemplateResponse(
             $this->appName,
             'index',
             []
            );

            // Set up Content Security Policy.
            $csp = new ContentSecurityPolicy();
            $csp->addAllowedConnectDomain('*');
            $response->setContentSecurityPolicy($csp);

            return $response;
        } catch (\Exception $e) {
            // Return an error template response if an exception occurs.
            return new TemplateResponse(
             $this->appName,
             'error',
             ['error' => $e->getMessage()],
             '500'
            );
        }//end try
    }//end page()

    /**
     * Read a single bericht.
     *
     * @param string $id The bericht ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function show(string $id): JSONResponse
    {
        // Fetch the object by its ID.
        $object = $this->objectService->getObject('berichten', $id);

        // Return the object as a JSON response.
        return new JSONResponse($object);
    }//end show()

    /**
     * Create a bericht.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function create(): JSONResponse
    {
        // Get all parameters from the request.
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new object.
        unset($data['id']);

        // Save the new object.
        $object = $this->objectService->saveObject('berichten', $data);

        // Return the created object as a JSON response.
        return new JSONResponse($object);
    }//end create()

    /**
     * Update a bericht.
     *
     * @param string $id The bericht ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function update(string $id): JSONResponse
    {
        // Get all parameters from the request.
        $data = $this->request->getParams();

        // Save the updated object.
        $object = $this->objectService->saveObject('berichten', $data);

        // Return the updated object as a JSON response.
        return new JSONResponse($object);
    }//end update()

    /**
     * Delete a bericht.
     *
     * @param string $id The bericht ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function destroy(string $id): JSONResponse
    {
        // Delete the object.
        $result = $this->objectService->deleteObject('berichten', $id);

        // Return the result as a JSON response.
        $status = ($result === true) ? 200 : 404;
        return new JSONResponse(['success' => $result], $status);
    }//end destroy()

    /**
     * Get audit trail for a specific bericht.
     *
     * @param string $id The bericht ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        $auditTrail = $this->objectService->getAuditTrail('berichten', $id);
        return new JSONResponse($auditTrail);
    }//end getAuditTrail()
}//end class
