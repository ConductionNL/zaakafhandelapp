<?php
namespace OCA\DsoNextcloud\Settings;

use OCA\DsoNextcloud\Collector;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\BackgroundJob\IJobList;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IL10N;
use OCP\Settings\ISettings;

use OCA\DsoNextcloud\AppInfo\Application;

class SharingSettings implements ISettings {

	/** @var Collector */
	private $collector;

	/** @var IConfig */
	private $config;

	/** @var IL10N */
	private $l;

	/** @var IDateTimeFormatter */
	private $dateTimeFormatter;

	/** @var IJobList */
	private $jobList;

	/**
	 * Admin constructor.
	 *
	 * @param Collector $collector
	 * @param IConfig $config
	 * @param IL10N $l
	 * @param IDateTimeFormatter $dateTimeFormatter
	 * @param IJobList $jobList
	 */
	public function __construct(Collector $collector,
								IConfig $config,
								IL10N $l,
								IDateTimeFormatter $dateTimeFormatter,
								IJobList $jobList
	) {
		$this->collector = $collector;
		$this->config = $config;
		$this->l = $l;
		$this->dateTimeFormatter = $dateTimeFormatter;
		$this->jobList = $jobList;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {

		// Getting the config
		$zakenLocation = $this->config->getAppValue(Application::APP_ID, 'zaken_location', null);
		$zakenKey = $this->config->getAppValue(Application::APP_ID, 'zaken_key', false);
		$takenLocation = $this->config->getAppValue(Application::APP_ID, 'taken_location', null);
		$takenKey = $this->config->getAppValue(Application::APP_ID, 'taken_key', null);
		$contactMomentenLocation = $this->config->getAppValue(Application::APP_ID, 'contact_momenten_location', null);
		$klantenLocation = $this->config->getAppValue(Application::APP_ID, 'klanten_location', null);
		$klantenKey = $this->config->getAppValue(Application::APP_ID, 'klanten_key', null);
		$zaakTypenLocation = $this->config->getAppValue(Application::APP_ID, 'zaak_typen_location', null);
		$zaakTypenKey = $this->config->getAppValue(Application::APP_ID, 'zaak_typen_key', null);

		$parameters = [
			'zakenLocation' => $zakenLocation,
			'zakenKey' => $zakenKey,
			'takenLocation' => $takenLocation,
			'takenKey' => $takenKey,
			'contactMomentenLocation' => $contactMomentenLocation,
			'klantenLocation' => $klantenLocation,
			'klantenKey' => $klantenKey,
			'zaakTypenLocation' => $zaakTypenLocation,
			'zaakTypenKey' => $zaakTypenKey,
		];

		return new TemplateResponse(Application::APP_ID, 'sharingSettings', $parameters);
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return 'sharing';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 */
	public function getPriority() {
		return 50;
	}

}
