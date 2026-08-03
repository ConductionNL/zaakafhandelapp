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
 * Geeft invulling aan https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZakenController extends Controller
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
     * Return (and serach) all objects
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-001
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

         // Retrieve all request parameters
        $requestParams = $this->request->getParams();

        // Fetch catalog objects based on filters and order
        $data = $this->objectService->getResultArrayForRequest('zaken', $requestParams);

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
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
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
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-002
     */
    public function show(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Fetch the catalog object by its ID
        $object = $this->objectService->getObject('zaken', $id);

        if ($object === null) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        // Return the catalog as a JSON response
        return new JSONResponse($object);
    }//end show()

    /**
     * Creatue an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new object
        unset($data['id']);

        // Strip system-managed ZGW fields that must be set server-side (ZGW API-principes).
        unset($data['bronorganisatie'], $data['verantwoordelijkeOrganisatie'], $data['identificatie'], $data['archiefstatus'], $data['created'], $data['updated']);

        // Default archiefstatus to 'nog_te_archiveren' for new zaken so that
        // ZGWZaakValidationService::checkArchivePrerequisites passes on deployments
        // whose schema does not define this default (C2 fix).
        $data['archiefstatus'] = 'nog_te_archiveren';

        // Save the new catalog object
        $object = $this->objectService->saveObject('zaken', $data);

        // Return the created object as a JSON response
        return new JSONResponse($object);
    }//end create()

    /**
     * Update an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Get all parameters from the request
        $data = $this->request->getParams();

        // Pin the ID from the URL to prevent IDOR: body-supplied id must not override path id.
        $data['id'] = $id;

        // Strip system-managed ZGW fields that must not be overwritten via the request body.
        unset($data['bronorganisatie'], $data['verantwoordelijkeOrganisatie'], $data['identificatie'], $data['archiefstatus'], $data['created'], $data['updated']);

        // Save the updated object
        $object = $this->objectService->saveObject('zaken', $data);

        // Return the created object as a JSON response
        return new JSONResponse($object);
    }//end update()

    /**
     * Delate an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-003
     */
    public function destroy(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Delete the catalog object
        $result = $this->objectService->deleteObject('zaken', $id);

        // Return the result as a JSON response
        return new JSONResponse(['success' => $result], $result === true ? 200 : 404);
    }//end destroy()

    /**
     * Get audit trail for a specific zaak
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-004
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // IDOR guard: verify the object exists and is accessible before returning its audit trail.
        $object = $this->objectService->getObject('zaken', $id);
        if ($object === null) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        $auditTrail = $this->objectService->getAuditTrail($id);
        return new JSONResponse($auditTrail);
    }//end getAuditTrail()
}//end class
