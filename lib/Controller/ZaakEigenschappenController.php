<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Controller for zaak eigenschappen (case properties) resources.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZaakEigenschappenController extends Controller
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
     * This returns the template of the main app's page
     * It adds some data to the template (app version)
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

    /** UUID v4 pattern used to validate path segments before interpolation into URLs. */
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /**
     * Validate that $value is a strict UUID to prevent path-traversal / SSRF (#270).
     *
     * @param string $value The value to validate.
     * @param string $field The field name for the error message.
     *
     * @return JSONResponse|null Returns a 400 response when invalid, null when valid.
     */
    private function assertUuid(string $value, string $field): ?JSONResponse
    {
        if (preg_match(self::UUID_PATTERN, $value) !== 1) {
            return new JSONResponse(
                ['error' => "$field must be a valid UUID"],
                Http::STATUS_BAD_REQUEST
            );
        }

        return null;
    }//end assertUuid()

    /**
     * Return (and serach) all objects
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function index(CallService $callService, string $zaakId): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $uuidError = $this->assertUuid($zaakId, 'zaakId');
        if ($uuidError !== null) {
            return $uuidError;
        }

        $results = $callService->index(source: 'zrc', endpoint: "zaken/$zaakId/zaakeigenschappen");
        return new JSONResponse($results);
    }//end index()

    /**
     * Read a single object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function show(string $id, CallService $callService, string $zaakId): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $uuidError = $this->assertUuid($zaakId, 'zaakId');
        if ($uuidError !== null) {
            return $uuidError;
        }

        $results = $callService->show(source: 'zrc', endpoint: "zaken/$zaakId/zaakeigenschappen", id: $id);
        return new JSONResponse($results);
    }//end show()

    /**
     * Creatue an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function create(CallService $callService, string $zaakId): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $uuidError = $this->assertUuid($zaakId, 'zaakId');
        if ($uuidError !== null) {
            return $uuidError;
        }

        // get post from requests
        $body    = $this->request->getParams();
        $results = $callService->create(source: 'zrc', endpoint: "zaken/$zaakId/zaakeigenschappen", data: $body);
        return new JSONResponse($results);
    }//end create()

    /**
     * Update an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function update(string $id, CallService $callService, string $zaakId): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $uuidError = $this->assertUuid($zaakId, 'zaakId');
        if ($uuidError !== null) {
            return $uuidError;
        }

        $body    = $this->request->getParams();
        $results = $callService->update(source: 'zrc', endpoint: "zaken/$zaakId/zaakeigenschappen", data: $body, id: $id);
        return new JSONResponse($results);
    }//end update()

    /**
     * Delate an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function destroy(string $id, CallService $callService, string $zaakId): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $uuidError = $this->assertUuid($zaakId, 'zaakId');
        if ($uuidError !== null) {
            return $uuidError;
        }

        $callService->destroy(source: 'zrc', endpoint: "zaken/$zaakId/zaakeigenschappen", id: $id);

        return new JSONResponse([]);
    }//end destroy()
}//end class
