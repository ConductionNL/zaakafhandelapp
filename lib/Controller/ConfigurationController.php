<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;

class ConfigurationController extends Controller
{
	public function __construct(
		$appName,
		IAppConfig $config,
		IRequest $request
	) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->request = $request;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): JSONResponse
	{

		$data = [];
		$defaults = [
		// Getting the config
			'drcLocation'=>'',
			'drcKey'=>'',
			'drcAuthType'=>'',
			'orcLocation'=>'',
			'orcKey'=>'',
			'orcAuthType'=>'',
			'zrcLocation'=>'',
			'zrcKey'=>'',
			'zrcAuthType'=>'',
			'ztcLocation'=>'',
			'ztcKey'=>'',
			'ztcAuthType'=>'',
			'brcLocation' => '',
			'brcKey'=>'',
			'brcAuthType'=>'',
			'klantenLocation'=> '',
			'klantenKey'=>'',
			'klantenAuthType'=>'',
			'elasticLocation'=>'',
			'elasticKey'=>'',
			'mongodbLocation'=>'',
			'mongodbKey'=>'',
			'mongodbCluster'=>'',
			'organisationName'=>'',
			'organisationOIN'=>'',
			'organisationPKI'=>'',
			'organisationRSIN'=>'',
			'organisationKVK'=>''
		];

		// We should filter out unwanted values before this
		foreach($defaults as $key => $value){
			$data[$key] =  $this->config->getValueString('zaakafhandelapp', $key, $value);
		}

		return new JSONResponse($data);
	}

	/**
	 * Handling the post request
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function create(): JSONResponse
	{
		$data = $this->request->getParams();

		// We should filter out unwanted values before this
		foreach($data as $key => $value){
			$this->config->setValueString('zaakafhandelapp', $key, $value);
			$data[$key] =  $this->config->getValueString('zaakafhandelapp', $key);
		}

		return new JSONResponse($data);
	}
}
