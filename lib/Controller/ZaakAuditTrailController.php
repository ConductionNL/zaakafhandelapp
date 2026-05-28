<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Stub controller for the ZGW audit-trail resource.
 *
 * Real audit-trail data has not yet been implemented. All data endpoints return
 * 501 Not Implemented so that audit systems and Archiefwet compliance checks
 * never receive fabricated test data in place of the real evidence trail (issue #268).
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ZaakAuditTrailController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly IAppConfig $config,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * This returns the template of the main app's page.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function page(): TemplateResponse
    {
        return new TemplateResponse(
            'zaakafhandelapp',
            'index',
            []
        );
    }//end page()

    /**
     * Return (and search) all objects.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end index()

    /**
     * Read a single object.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function show(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end show()

    /**
     * Create an object.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end create()

    /**
     * Update an object.
     *
     * $id is part of the NC route signature; this stub returns 501 Not Implemented.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end update()

    /**
     * Delete an object.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $id is part of the NC route signature.
     */
    public function destroy(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end destroy()
}//end class
