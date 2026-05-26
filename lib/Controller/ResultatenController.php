<?php

namespace OCA\ZaakAfhandelApp\Controller;

use GuzzleHttp\Client;
use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

/**
 * Geeft invulling aan https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ResultatenController extends Controller
{
    const TEST_ARRAY = [
        "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f" => [
            "id"      => "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
            "name"    => "Github",
            "summary" => "summary for one",
        ],
        "4c3edd34-a90d-4d2a-8894-adb5836ecde8" => [
            "id"      => "4c3edd34-a90d-4d2a-8894-adb5836ecde8",
            "name"    => "Gitlab",
            "summary" => "summary for two",
        ],
        "15551d6f-44e3-43f3-a9d2-59e583c91eb0" => [
            "id"      => "15551d6f-44e3-43f3-a9d2-59e583c91eb0",
            "name"    => "Woo",
            "summary" => "summary for two",
        ],
        "0a3a0ffb-dc03-4aae-b207-0ed1502e60da" => [
            "id"      => "0a3a0ffb-dc03-4aae-b207-0ed1502e60da",
            "name"    => "Decat",
            "summary" => "summary for two",
        ],
    ];

    public function __construct(
        $appName,
        IRequest $request,
        private readonly IAppConfig $config
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
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-003
     */
    public function pages(): TemplateResponse
    {
        return new TemplateResponse(
            // Application::APP_ID,
            'zaakafhandelapp',
            'index',
            []
        );
    }//end pages()

    /**
     * Return (and serach) all objects
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
      *
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function index(CallService $callService): JSONResponse
    {
        $results = $callService->index(source: 'zrc', endpoint: 'resultaten');
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
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function show(string $id, CallService $callService): JSONResponse
    {
        $results = $callService->show(source: 'zrc', endpoint: 'resultaten', id: $id);
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
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function create(CallService $callService): JSONResponse
    {
        // get post from requests
        $body    = $this->request->getParams();
        $results = $callService->create(source: 'zrc', endpoint: 'resultaten', data: $body);
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
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function update(string $id, CallService $callService): JSONResponse
    {
        $body    = $this->request->getParams();
        $results = $callService->update(source: 'zrc', endpoint: 'resultaten', data: $body, id: $id);
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
      * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function destroy(string $id, CallService $callService): JSONResponse
    {
        $callService->destroy(source: 'zrc', endpoint: 'resultaten', id: $id);

        return new JSONResponse([]);
    }//end destroy()
}//end class
