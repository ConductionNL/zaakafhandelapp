<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\MailService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

/**
 * Controller for handling tasks (taken) operations.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class TakenController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly MailService $mailService,
        private readonly ObjectService $objectService,
        private readonly IUserSession $userSession,
        private readonly LoggerInterface $logger,
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $getParameter is an NC route param
     *   reserved for future SPA deep-linking; the PHP layer renders a shell template only.
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
                TemplateResponse::RENDER_AS_ERROR,
                Http::STATUS_INTERNAL_SERVER_ERROR
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

        try {
            // Fetch the catalog object by its ID.
            $object = $this->objectService->getObject('taken', $id);

            if ($object === null) {
                return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
            }

            // Return the catalog as a JSON response.
            return new JSONResponse($object);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read taak: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not read taak'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }//end try
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

        try {
            // Get all parameters from the request.
            $data = $this->request->getParams();

            // Remove the 'id' field if it exists, as we're creating a new object.
            unset($data['id']);

            // Save the new catalog object.
            $object = $this->objectService->saveObject('taken', $data);

            $this->mailService->sendMail([], is_array($object) === true ? $object : $object->jsonSerialize());

            // Return the created object as a JSON response.
            return new JSONResponse($object);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to create taak: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not create taak'], Http::STATUS_BAD_REQUEST);
        }//end try
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $id is part of the NC route signature;
     *   the full payload is consumed via $this->request->getParams() instead.
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            // Get all parameters from the request.
            $data = $this->request->getParams();

            $oldObject = $this->objectService->getObject('taken', $id);

            // An unknown id yields null here; dereferencing it for the mail
            // diff below is a fatal Error, so answer 404 first.
            if ($oldObject === null) {
                return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
            }

            $data['id'] = $id;

            // Save the new catalog object.
            $object = $this->objectService->saveObject('taken', $data);

            $this->mailService->sendMail(
                is_array($oldObject) === true ? $oldObject : $oldObject->jsonSerialize(),
                is_array($object) === true ? $object : $object->jsonSerialize()
            );

            // Return the created object as a JSON response.
            return new JSONResponse($object);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to update taak: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not update taak'], Http::STATUS_BAD_REQUEST);
        }//end try
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

        try {
            // Delete the catalog object.
            $result = $this->objectService->deleteObject('taken', $id);

            // Return the result as a JSON response.
            return new JSONResponse(['success' => $result], $result === true ? Http::STATUS_OK : Http::STATUS_NOT_FOUND);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to delete taak: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not delete taak'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }//end try
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

        try {
            // IDOR guard: verify the object exists and is accessible before returning its audit trail.
            $object = $this->objectService->getObject('taken', $id);
            if ($object === null) {
                return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
            }

            $auditTrail = $this->objectService->getAuditTrail($id);
            return new JSONResponse($auditTrail);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read taak audit trail: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not read audit trail'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }//end try
    }//end getAuditTrail()
}//end class
