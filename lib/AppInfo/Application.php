<?php

namespace OCA\ZaakAfhandelApp\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCA\ZaakAfhandelApp\Dashboard\ZakenWidget;
use OCA\ZaakAfhandelApp\Dashboard\TakenWidget;
use OCA\ZaakAfhandelApp\Dashboard\OpenZakenWidget;
use OCA\ZaakAfhandelApp\Dashboard\ContactmomentenWidget;
use OCA\ZaakAfhandelApp\Dashboard\PersonenWidget;
use OCA\ZaakAfhandelApp\Dashboard\OrganisatiesWidget;

/**
 * Class Application
 *
 * @package OCA\ZaakAfhandelApp\AppInfo
 */
class Application extends App implements IBootstrap
{
	public const APP_ID = 'zaakafhandelapp';

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = [])
	{
		parent::__construct(appName: self::APP_ID, urlParams: $urlParams);
	}

	public function register(IRegistrationContext $context): void
	{
		$context->registerDashboardWidget(ZakenWidget::class);
		$context->registerDashboardWidget(TakenWidget::class);
		$context->registerDashboardWidget(OpenZakenWidget::class);
		$context->registerDashboardWidget(ContactmomentenWidget::class);
		$context->registerDashboardWidget(PersonenWidget::class);
		$context->registerDashboardWidget(OrganisatiesWidget::class);

        $context->registerEventListener(
            \OCA\OpenRegister\Event\ObjectCreatedEvent::class,
            \OCA\ZaakAfhandelApp\EventListener\ZaakRegisterEventListener::class
        );
        $context->registerEventListener(
            \OCA\OpenRegister\Event\ObjectUpdatedEvent::class,
            \OCA\ZaakAfhandelApp\EventListener\ZaakRegisterEventListener::class
        );
        $context->registerEventListener(
            \OCA\OpenRegister\Event\ObjectDeletedEvent::class,
            \OCA\ZaakAfhandelApp\EventListener\ZaakRegisterEventListener::class
        );
	}

	public function boot(IBootContext $context): void
	{
	}
}
