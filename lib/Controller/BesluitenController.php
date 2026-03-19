<?php
/**
 * Controller for handling besluiten operations.
 *
 * @category Controller
 * @package  OCA\ZaakAfhandelApp\Controller
 * @author   Conduction b.v. <info@conduction.nl>
 * @license  EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link     https://conduction.nl
 */

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
 */
class BesluitenController extends Controller
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

    /**
     * Constructor for BesluitenController.
     *
     * @param string     $appName The app name.
     * @param IRequest   $request The request object.
     * @param IAppConfig $config  The app configuration.
     */
    public function __construct(
        $appName,
        IRequest $request,
        private readonly IAppConfig $config
    ) {
        parent::__construct(appName: $appName, request: $request);
    }//end __construct()

    /**
     * This returns the template of the main app's page.
     * It adds some data to the template (app version).
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
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
     * Return (and search) all besluiten.
     *
     * @param CallService $callService The call service.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function index(CallService $callService): JSONResponse
    {
        // Later concern.
        $query = $this->request->getParams();

        $results = $callService->index(source: 'brc', endpoint: 'besluiten');
        return new JSONResponse($results);
    }//end index()

    /**
     * Read a single besluit.
     *
     * @param string      $id          The besluit ID.
     * @param CallService $callService The call service.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function show(string $id, CallService $callService): JSONResponse
    {
        // Later concern.
        $query = $this->request->getParams();

        $results = $callService->show(source: 'brc', endpoint: 'besluiten', id: $id);
        return new JSONResponse($results);
    }//end show()

    /**
     * Create a besluit.
     *
     * @param CallService $callService The call service.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function create(CallService $callService): JSONResponse
    {
        // Get post from requests.
        $body    = $this->request->getParams();
        $results = $callService->create(source: 'brc', endpoint: 'besluiten', data: $body);
        return new JSONResponse($results);
    }//end create()

    /**
     * Update a besluit.
     *
     * @param string      $id          The besluit ID.
     * @param CallService $callService The call service.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function update(string $id, CallService $callService): JSONResponse
    {
        $body    = $this->request->getParams();
        $results = $callService->update(source: 'brc', endpoint: 'besluiten', data: $body, id: $id);
        return new JSONResponse($results);
    }//end update()

    /**
     * Delete a besluit.
     *
     * @param string      $id          The besluit ID.
     * @param CallService $callService The call service.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function destroy(string $id, CallService $callService): JSONResponse
    {
        $callService->destroy(source: 'brc', endpoint: 'besluiten', id: $id);

        return new JSONResponse([]);
    }//end destroy()
}//end class
