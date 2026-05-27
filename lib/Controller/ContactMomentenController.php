<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IUserSession;

/**
 * Controller for handling contact moments (contactmomenten) operations
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ContactMomentenController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Return (and search) all contact moments
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-001
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

         // Retrieve all request parameters
         $requestParams = $this->request->getParams();

         // Fetch contact moments based on filters and order
         $data = $this->objectService->getResultArrayForRequest('contactmomenten', $requestParams);

         // Return JSON response
         return new JSONResponse($data);
    }//end index()

    /**
     * Render no page.
     *
     * @param  string|null $getParameter Optional GET parameter
     * @return TemplateResponse The rendered template response
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-004
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $getParameter is an NC route param
     *   reserved for future SPA deep-linking; the PHP layer renders a shell template only.
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
        }//end try
    }//end page()

    /**
     * Read a single contact moment
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-001
     */
    public function show(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Fetch the contact moment by its ID
        $object = $this->objectService->getObject('contactmomenten', $id);

        // Return the contact moment as a JSON response
        return new JSONResponse($object);
    }//end show()

    /**
     * Create a contact moment
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new contact moment
        unset($data['id']);

        // Save the new contact moment
        $object = $this->objectService->saveObject('contactmomenten', $data);

        // Return the created contact moment as a JSON response
        return new JSONResponse($object);
    }//end create()

    /**
     * Update a contact moment
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $id is part of the NC route signature;
     *   the full payload is consumed via $this->request->getParams() instead.
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Get all parameters from the request
        $data = $this->request->getParams();

        // Save the updated contact moment
        $object = $this->objectService->saveObject('contactmomenten', $data);

        // Return the updated contact moment as a JSON response
        return new JSONResponse($object);
    }//end update()

    /**
     * Delete a contact moment
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function destroy(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Delete the contact moment
        $result = $this->objectService->deleteObject('contactmomenten', $id);

        // Return the result as a JSON response
        return new JSONResponse(['success' => $result], $result === true ? 200 : 404);
    }//end destroy()

    /**
     * Get audit trail for a specific contact moment
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-004
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $auditTrail = $this->objectService->getAuditTrail('contactmomenten', $id);
        return new JSONResponse($auditTrail);
    }//end getAuditTrail()
}//end class
