<?php

namespace OCA\ZaakAfhandelApp\Controller;

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
 * Controller for handling messages (berichten) operations.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class BerichtenController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly IUserSession $userSession,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Return (and serach) all objects
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

         // Fetch catalog objects based on filters and order
         $data = $this->objectService->getResultArrayForRequest('berichten', $requestParams);

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
             TemplateResponse::RENDER_AS_ERROR,
             Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }//end try
    }//end page()

    /**
     * Read a single object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @throws DoesNotExistException Translated to 404 below; never propagated.
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-001
     */
    public function show(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            // Fetch the catalog object by its ID
            $object = $this->objectService->getObject('berichten', $id);

            // Return the catalog as a JSON response
            return new JSONResponse($object);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read bericht: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not read bericht'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }//end try
    }//end show()

    /**
     * Creatue an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @throws \Throwable Translated to a JSON error below; never propagated.
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            // Get all parameters from the request
            $data = $this->request->getParams();

            // Remove the 'id' field if it exists, as we're creating a new object
            unset($data['id']);

            // Save the new catalog object
            $object = $this->objectService->saveObject('berichten', $data);

            // Return the created object as a JSON response
            return new JSONResponse($object);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to create bericht: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not create bericht'], Http::STATUS_BAD_REQUEST);
        }//end try
    }//end create()

    /**
     * Update an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @throws DoesNotExistException Translated to 404 below; never propagated.
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            // Get all parameters from the request
            $data = $this->request->getParams();

            // Pin the ID from the URL to prevent IDOR: body-supplied id must not override path id.
            $data['id'] = $id;

            // Strip server-managed fields that callers must not overwrite directly.
            unset($data['created'], $data['updated']);

            // Save the updated object
            $object = $this->objectService->saveObject('berichten', $data);

            // Return the created object as a JSON response
            return new JSONResponse($object);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to update bericht: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not update bericht'], Http::STATUS_BAD_REQUEST);
        }//end try
    }//end update()

    /**
     * Delate an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @throws DoesNotExistException Translated to 404 below; never propagated.
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function destroy(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            // Delete the catalog object
            $result = $this->objectService->deleteObject('berichten', $id);

            // Return the result as a JSON response
            return new JSONResponse(['success' => $result], $result === true ? Http::STATUS_OK : Http::STATUS_NOT_FOUND);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to delete bericht: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not delete bericht'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }//end try
    }//end destroy()

    /**
     * Get audit trail for a specific klant
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @throws DoesNotExistException Translated to 404 below; never propagated.
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
            $object = $this->objectService->getObject('berichten', $id);
            if ($object === null) {
                return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
            }

            $auditTrail = $this->objectService->getAuditTrail($id);
            return new JSONResponse($auditTrail);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to read bericht audit trail: '.$e->getMessage(), ['exception' => $e, 'app' => $this->appName]);
            return new JSONResponse(['error' => 'Could not read audit trail'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }//end try
    }//end getAuditTrail()
}//end class
