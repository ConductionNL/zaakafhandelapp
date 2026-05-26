<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\MailService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IUserSession;

/**
 * Controller for handling tasks (taken) operations.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class TakenController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly MailService $mailService,
        private readonly ObjectService $objectService,
        private readonly IUserSession $userSession,
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
      *
      * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-001
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Retrieve all request parameters.
        $requestParams = $this->request->getParams();

        // Fetch catalog objects based on filters and order.
        $data = $this->objectService->getResultArrayForRequest('taken', $requestParams);

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
      *
      * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-004
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
     * Read a single object.
     *
     * @param string $id The object ID.
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

        // Fetch the catalog object by its ID.
        $object = $this->objectService->getObject('taken', $id);

        // Return the catalog as a JSON response.
        return new JSONResponse($object);
    }//end show()

    /**
     * Create an object.
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

        // Get all parameters from the request.
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new object.
        unset($data['id']);

        // Save the new catalog object.
        $object = $this->objectService->saveObject('taken', $data);

        $this->mailService->sendMail([], is_array($object) === true ? $object : $object->jsonSerialize());

        // Return the created object as a JSON response.
        return new JSONResponse($object);
    }//end create()

    /**
     * Update an object.
     *
     * @param string $id The object ID.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
      *
      * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Get all parameters from the request.
        $data = $this->request->getParams();

        $oldObject = $this->objectService->getObject('taken', $id);

        $data['id'] = $id;

        // Save the new catalog object.
        $object = $this->objectService->saveObject('taken', $data);

        $this->mailService->sendMail(
            is_array($oldObject) === true ? $oldObject : $oldObject->jsonSerialize(),
            is_array($object) === true ? $object : $object->jsonSerialize()
        );

        // Return the created object as a JSON response.
        return new JSONResponse($object);
    }//end update()

    /**
     * Delete an object.
     *
     * @param string $id The object ID.
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

        // Delete the catalog object.
        $result = $this->objectService->deleteObject('taken', $id);

        // Return the result as a JSON response.
        return new JSONResponse(['success' => $result], $result === true ? 200 : 404);
    }//end destroy()

    /**
     * Get audit trail for a specific task.
     *
     * @param string $id The task ID.
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

        $auditTrail = $this->objectService->getAuditTrail('taken', $id);
        return new JSONResponse($auditTrail);
    }//end getAuditTrail()
}//end class
