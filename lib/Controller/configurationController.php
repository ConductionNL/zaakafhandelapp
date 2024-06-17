<?php

namespace OCA\DsoNextcloud\Controller;

use OCP\IConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

use OCA\DsoNextcloud\AppInfo\Application;

class ConfigurationController extends Controller
{

	/**
	 * @var IConfig
	 */
	private $config;


	public function __construct(
		string $appName,
		IRequest $request,
		IConfig $config
	) {
		parent::__construct($appName, $request);
		$this->config = $config;
	}

	/**
	 * This returns the template of the main app's page
	 * It adds some data to the template (app version)
	 *
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function index(): TemplateResponse
	{
		return new TemplateResponse(Application::APP_ID, "configurationIndex", []);
	}

	/**
	 * This returns the template of the main app's page
	 * It adds some data to the template (app version)
	 *
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function update(): TemplateResponse
	{
		return new TemplateResponse(Application::APP_ID, "zakenIndex", []);
	}
}
