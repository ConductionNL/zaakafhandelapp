<?php

namespace OCA\DsoNextcloud\Controller;

use GuzzleHttp\Client;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IAppConfig;

use OCA\DsoNextcloud\AppInfo\Application;

class ZakenController extends Controller
{

	/**
	 * @var IConfig
	 */
	private $config;


	public function __construct(
		string $appName,
		IRequest $request,
		IAppConfig $config
	) {
		parent::__construct($appName, $request);
		$this->config = $config;
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
	public function index(): TemplateResponse
	{
		return new TemplateResponse(Application::APP_ID, "zakenIndex", []);
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
	public function detail(string $id): TemplateResponse
	{
		return new TemplateResponse(Application::APP_ID, "zakenDetail", ['id' => $id]);
	}

	/**
	 * This passes zaken api requests to the zaken api and adds a key
	 *
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string $title
	 * @param string $content
	 *
	 * @return JSONResponse
	 */
	public function api($id): JSONResponse
	{
		// This (very obviusly) should be a service
		$zakenLocation = $this->config->getValueString(Application::APP_ID, 'zaken_location');
		$zakenKey = $this->config->getValueString(Application::APP_ID, 'zaken_key');

		// Lets add the id if provided
		if($id){
			$zakenLocation = $zakenLocation.'/'.$id;
		}

		// Lets default the request type
		$requestType = $this->request->getMethod();
		$data = $this->request->getParam();
		$headers = $this->request->getHeaders();
		$headers['authorization'] = $zakenKey;

		// Temp  test stuff REMOVE AFTHER TESTING
		$zakenLocation = 'https://api.github.com/repos/guzzle/guzzle';
		$zakenKey = 'asdas';

		// Lets make the call based on https://docs.guzzlephp.org/en/5.3/quickstart.html?highlight=json
		$client = new Client();

		$response = $client->request($requestType, $zakenLocation, ['headers' => $headers,'json' => $data]);

		// Bit of pass trough
		$data = json_decode($response->getBody());
		$status = $response->getStatusCode();
		$headers = $response->getHeaders();

		return new JSONResponse($data,$status,$headers);
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
	public function create(string $id): TemplateResponse
	{
		$appVersion = $this->config->getAppValue(Application::APP_ID, 'installed_version');

		return new JSONResponse(
			Application::APP_ID,
			'index',
			[
				'app_version' => $appVersion,
			]
		);
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
	public function delete(string $id): TemplateResponse
	{
		$appVersion = $this->config->getAppValue(Application::APP_ID, 'installed_version');
		return new TemplateResponse(
			Application::APP_ID,
			'index',
			[
				'app_version' => $appVersion,
			]
		);
	}
}
