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
 * Stub controller for the ZGW documenten resource.
 *
 * Geeft invulling aan https://vng-realisatie.github.io/gemma-zaken/standaard/documenten/
 *
 * Real documenten data has not yet been implemented. All data endpoints return
 * 501 Not Implemented so that clients never receive fabricated test data in place
 * of real DRC document records (issue #268).
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class DocumentenController extends Controller
{
    public function __construct(
        string $appName,
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
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-003
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
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Documenten is not yet implemented.'],
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
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function show(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Documenten is not yet implemented.'],
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
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Documenten is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end create()

    /**
     * Update an object.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Documenten is not yet implemented.'],
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
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $id is part of the NC route signature.
     */
    public function destroy(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        return new JSONResponse(
            ['error' => 'Documenten is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end destroy()
}//end class
