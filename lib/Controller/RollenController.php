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
 */
class RollenController extends Controller
{

	/**
	 * RollenController constructor.
	 *
	 * @param string $appName The application name
	 * @param IRequest $request The request object
	 * @param IAppConfig $config The app configuration
	 */
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IAppConfig $config
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Renders the main application page.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function page(): TemplateResponse
	{
		return new TemplateResponse('zaakafhandelapp', 'index', []);
	}

	/**
	 * Return (and search) all rollen.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param CallService $callService The call service
	 *
	 * @return JSONResponse
	 */
	public function index(CallService $callService): JSONResponse
	{
		$results = $callService->index(source: 'zrc', endpoint: 'rollen');
		return new JSONResponse($results);
	}

	/**
	 * Read a single rol.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $id The rol ID
	 * @param CallService $callService The call service
	 *
	 * @return JSONResponse
	 */
	public function show(string $id, CallService $callService): JSONResponse
	{
		$results = $callService->show(source: 'zrc', endpoint: 'rollen', id: $id);
		return new JSONResponse($results);
	}

	/**
	 * Create a rol.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param CallService $callService The call service
	 *
	 * @return JSONResponse
	 */
	public function create(CallService $callService): JSONResponse
	{
		$body = $this->request->getParams();
		$results = $callService->create(source: 'zrc', endpoint: 'rollen', data: $body);
		return new JSONResponse($results);
	}

	/**
	 * Update a rol.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $id The rol ID
	 * @param CallService $callService The call service
	 *
	 * @return JSONResponse
	 */
	public function update(string $id, CallService $callService): JSONResponse
	{
		$body = $this->request->getParams();
		$results = $callService->update(source: 'zrc', endpoint: 'rollen', data: $body, id: $id);
		return new JSONResponse($results);
	}

	/**
	 * Delete a rol.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $id The rol ID
	 * @param CallService $callService The call service
	 *
	 * @return JSONResponse
	 */
	public function destroy(string $id, CallService $callService): JSONResponse
	{
		$callService->destroy(source: 'zrc', endpoint: 'rollen', id: $id);
		return new JSONResponse([]);
	}
}
