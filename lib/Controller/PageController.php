<?php

namespace OCA\DsoNextcloud\Controller;

use OCP\IConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

use OCA\DsoNextcloud\AppInfo\Application;

class PageController extends Controller
{

	/**
	 * @var IConfig
	 */
	private $config;


	public function __construct(
		string $appName,
		IRequest $request,
		IConfig $config,
	) {
		parent::__construct($appName, $request);
		$this->config = $config;
	}


	public function mainPage(): TemplateResponse
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
