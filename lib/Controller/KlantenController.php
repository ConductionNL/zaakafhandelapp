<?php

namespace OCA\ZaakAfhandelApp\Controller;

use GuzzleHttp\Client;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\IAppConfig;
use OCP\IRequest;

class KlantenController extends Controller
{

    public function __construct
	(
		$appName,
		IRequest $request,
		private readonly IAppConfig $config
	)
    {
        parent::__construct($appName, $request);
    }

	/**
	 * This returns the template of the main app's page
	 * It adds some data to the template (app version)
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function page(): TemplateResponse
	{
        return new TemplateResponse(
            //Application::APP_ID,
            'zaakafhandelapp',
            'index',
            []
        );
	}


    /**
     * Return (and serach) all objects
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function index(CallService $klantenService): JSONResponse
    {
        // Latere zorg
        $query= $this->request->getParams();

        $results = $klantenService->index(source: 'klanten', endpoint: 'klanten');
        return new JSONResponse($results);
    }

    /**
     * Read a single object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function show(string $id, CallService $klantenService): JSONResponse
    {
        // Latere zorg
        $query= $this->request->getParams();

		$results = $klantenService->show(source: 'klanten', endpoint: 'klanten', id: $id);
        return new JSONResponse($results);
    }


    /**
     * Creatue an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function create(CallService $callService): JSONResponse
    {
        // get post from requests
		$body = $this->request->getParams();
        $results = $callService->create(source: 'klanten', endpoint: 'klanten', data: $body);
        return new JSONResponse($results);
    }

    /**
     * Update an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function update(string $id, CallService $callService): JSONResponse
    {
		$body = $this->request->getParams();
		$results = $callService->update(source: 'klanten', endpoint: 'klanten', data: $body, id: $id);
		return new JSONResponse($results);
    }

    /**
     * Delate an object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
	 *
	 * @return JSONResponse
     */
    public function destroy(string $id, CallService $callService): JSONResponse
    {
		$callService->destroy(source: 'klanten', endpoint: 'klanten', id: $id);

		return new JsonResponse([]);
    }
}
