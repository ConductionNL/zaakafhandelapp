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
 * Controller for zaakinformatieobjecten (case document links) resources.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZaakInformatieObjectenController extends Controller
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
    public function index(CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $results = $callService->index(source: 'zrc', endpoint: 'zaakinformatieobjecten');
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
    public function show(string $id, CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $results = $callService->show(source: 'zrc', endpoint: 'zaakinformatieobjecten', id: $id);
        return new JSONResponse($results);
    }//end show()

    /**
     * Validate that the `zaak` and `informatieobject` URL fields are present and syntactically valid.
     *
     * Shared by both create and update so that the same hardening applied in #289 is enforced
     * on partial updates as well.
     *
     * @param array $body The request payload.
     *
     * @return JSONResponse|null A 400 response on the first invalid field, null when all fields are valid.
     */
    private function validateZioUrls(array $body): ?JSONResponse
    {
        foreach (['zaak', 'informatieobject'] as $field) {
            if (empty($body[$field]) === true) {
                return new JSONResponse(
                    [$field => ["Het veld $field is verplicht."]],
                    Http::STATUS_BAD_REQUEST
                );
            }

            if (filter_var($body[$field], FILTER_VALIDATE_URL) === false) {
                return new JSONResponse(
                    [$field => ["Het veld $field moet een geldige URL zijn."]],
                    Http::STATUS_BAD_REQUEST
                );
            }
        }

        return null;
    }//end validateZioUrls()

    /**
     * Create a ZIO (zaak-informatieobject link).
     *
     * Guards (#280):
     * - Both `zaak` and `informatieobject` URL fields must be present and non-empty.
     * - Both values must be syntactically valid HTTP(S) URLs.
     *
     * Note: full object-level authorization (verify the current user has write access on the
     * zaak and read access on the informatieobject) requires a ZRC/DRC look-up that is beyond
     * the scope of this controller. That check is tracked in GitHub issue #280 and should be
     * implemented once per-object ACLs are available via the configured ZRC/DRC sources.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function create(CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // get post from requests
        $body = $this->request->getParams();

        // Validate that required URL fields are present and syntactically valid (#280).
        $urlError = $this->validateZioUrls($body);
        if ($urlError !== null) {
            return $urlError;
        }

        $results = $callService->create(source: 'zrc', endpoint: 'zaakinformatieobjecten', data: $body);
        return new JSONResponse($results);
    }//end create()

    /**
     * Update an object
     *
     * Applies the same URL-field validation as create (#289 hardening) so that
     * update payloads with invalid zaak/informatieobject URLs are rejected before
     * they reach the ZRC backend.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function update(string $id, CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $body = $this->request->getParams();

        // Validate URL fields on update as well as create (#289).
        $urlError = $this->validateZioUrls($body);
        if ($urlError !== null) {
            return $urlError;
        }

        $results = $callService->update(source: 'zrc', endpoint: 'zaakinformatieobjecten', data: $body, id: $id);
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
    public function destroy(string $id, CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $callService->destroy(source: 'zrc', endpoint: 'zaakinformatieobjecten', id: $id);

        return new JSONResponse([]);
    }//end destroy()
}//end class
