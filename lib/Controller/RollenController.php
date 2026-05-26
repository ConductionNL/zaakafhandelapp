<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

/**
 * Controller for rollen (roles) resources.
 *
 * @see https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class RollenController extends Controller
{
    /**
     * RollenController constructor.
     *
     * @param string     $appName The application name
     * @param IRequest   $request The request object
     * @param IAppConfig $config  The app configuration
     */
    public function __construct(
        $appName,
        IRequest $request,
        private readonly IAppConfig $config
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Renders the main application page.
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
        return new TemplateResponse('zaakafhandelapp', 'index', []);
    }//end page()

    /**
     * Return (and search) all rollen.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param CallService $callService The call service
     *
     * @return JSONResponse
      *
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function index(CallService $callService): JSONResponse
    {
        $results = $callService->index(source: 'zrc', endpoint: 'rollen');
        return new JSONResponse($results);
    }//end index()

    /**
     * Read a single rol.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string      $id          The rol ID
     * @param CallService $callService The call service
     *
     * @return JSONResponse
      *
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function show(string $id, CallService $callService): JSONResponse
    {
        $results = $callService->show(source: 'zrc', endpoint: 'rollen', id: $id);
        return new JSONResponse($results);
    }//end show()

    /**
     * Create a rol.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param CallService $callService The call service
     *
     * @return JSONResponse
      *
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function create(CallService $callService): JSONResponse
    {
        $body    = $this->request->getParams();
        $results = $callService->create(source: 'zrc', endpoint: 'rollen', data: $body);
        return new JSONResponse($results);
    }//end create()

    /**
     * Update a rol.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string      $id          The rol ID
     * @param CallService $callService The call service
     *
     * @return JSONResponse
      *
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function update(string $id, CallService $callService): JSONResponse
    {
        $body    = $this->request->getParams();
        $results = $callService->update(source: 'zrc', endpoint: 'rollen', data: $body, id: $id);
        return new JSONResponse($results);
    }//end update()

    /**
     * Delete a rol.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string      $id          The rol ID
     * @param CallService $callService The call service
     *
     * @return JSONResponse
      *
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function destroy(string $id, CallService $callService): JSONResponse
    {
        $callService->destroy(source: 'zrc', endpoint: 'rollen', id: $id);
        return new JSONResponse([]);
    }//end destroy()
}//end class
