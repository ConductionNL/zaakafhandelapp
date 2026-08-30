<?php

namespace OCA\ZaakAfhandelApp\AppInfo;

use OCA\ZaakAfhandelApp\Dashboard\ContactmomentenWidget;
use OCA\ZaakAfhandelApp\Dashboard\OpenZakenWidget;
use OCA\ZaakAfhandelApp\Dashboard\OrganisatiesWidget;
use OCA\ZaakAfhandelApp\Dashboard\PersonenWidget;
use OCA\ZaakAfhandelApp\Dashboard\TakenWidget;
use OCA\ZaakAfhandelApp\Dashboard\ZakenWidget;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;

/**
 * Class Application
 *
 * @package OCA\ZaakAfhandelApp\AppInfo
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class Application extends App implements IBootstrap {
	public const APP_ID = 'zaakafhandelapp';

	/**
	 * OpenRegister object-lifecycle events ZaakRegisterEventListener observes.
	 *
	 * @var array<int,string>
	 */
	private const OBJECT_EVENTS = [
		\OCA\OpenRegister\Event\ObjectCreatedEvent::class,
		\OCA\OpenRegister\Event\ObjectUpdatedEvent::class,
		\OCA\OpenRegister\Event\ObjectDeletedEvent::class,
		\OCA\OpenRegister\Event\ObjectCreatingEvent::class,
		\OCA\OpenRegister\Event\ObjectUpdatingEvent::class,
		\OCA\OpenRegister\Event\ObjectDeletingEvent::class,
	];

	/**
	 * Schema slugs ZaakRegisterEventListener acts on.
	 *
	 * The union of every slug tested by handleObjectCreating/Created/
	 * Updating/Updated/Deleted, read off ZGWRegistryService::SCHEMAS — the
	 * single hardcoded source those handlers compare against. Slug matching in
	 * OpenRegister's subscription is case-insensitive, so the lowercase
	 * `zaaktypeinformatieobjecttype` here still resolves the instance's
	 * `zaaktypeInformatieobjecttype` schema.
	 *
	 * @var array<int,string>
	 */
	private const ZGW_SCHEMAS = [
		'zaak',
		'status',
		'besluit',
		'zaakinformatieobject',
		'besluitinformatieobject',
		'zaaktypeinformatieobjecttype',
	];

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct(appName: self::APP_ID, urlParams: $urlParams);
	}//end __construct()

	public function register(IRegistrationContext $context): void {
		$context->registerDashboardWidget(ZakenWidget::class);
		$context->registerDashboardWidget(TakenWidget::class);
		$context->registerDashboardWidget(OpenZakenWidget::class);
		$context->registerDashboardWidget(ContactmomentenWidget::class);
		$context->registerDashboardWidget(PersonenWidget::class);
		$context->registerDashboardWidget(OrganisatiesWidget::class);
	}//end register()

	/**
	 * Register an object-lifecycle listener that declares its interest up front.
	 *
	 * OpenRegister's `ObjectEventSubscription` records the register/schema slugs
	 * a listener reacts to and routes dispatches through a single shared proxy,
	 * so an uninterested listener is neither constructed nor invoked. When
	 * OpenRegister is absent — ZaakAfhandelApp carries no hard dependency on it
	 * — this degrades to the plain global registration it replaced, which is
	 * exactly the behaviour every listener had before.
	 *
	 * This MUST be called from boot(), never from register(). Nextcloud enables
	 * each app's autoloader immediately before calling that app's own
	 * register(), so during register() OpenRegister's classes are only
	 * autoloadable to apps that register after it — the class_exists() guard
	 * below would silently resolve to false purely because of this app's
	 * position in the enabled-app list, and the unfiltered fallback would look
	 * identical to a working narrowing. boot() runs only after every app's
	 * register() has completed, so the guard resolves regardless of ordering.
	 *
	 * @param IEventDispatcher $dispatcher The live event dispatcher.
	 * @param string $event OpenRegister event class name.
	 * @param string $listener Listener class name.
	 * @param array<int,string>|null $registers Register slugs, or null for all.
	 * @param array<int,string>|null $schemas Schema slugs, or null for all.
	 *
	 * @return void
	 */
	private function registerFilteredObjectListener(
		IEventDispatcher $dispatcher,
		string $event,
		string $listener,
		?array $registers,
		?array $schemas,
	): void {
		$subscription = '\\OCA\\OpenRegister\\Event\\ObjectEventSubscription';
		if (class_exists($subscription) === true) {
			$subscription::subscribe(
				dispatcher: $dispatcher,
				event: $event,
				listener: $listener,
				registers: $registers,
				schemas: $schemas
			);
			return;
		}

		// Loud on purpose. This fallback is correct but UNFILTERED, and while it
		// was silent it was indistinguishable from a working narrowing.
		\OCP\Server::get(\Psr\Log\LoggerInterface::class)->warning(
			'OpenRegister ObjectEventSubscription unavailable: ' . $listener
			. ' fell back to an UNFILTERED registration for ' . $event
			. ' and will be invoked on every object write instance-wide.',
			['app' => self::APP_ID]
		);

		$dispatcher->addServiceListener($event, $listener);
	}//end registerFilteredObjectListener()

	/**
	 * Boot the application.
	 *
	 * @param IBootContext $context The boot context.
	 */
	public function boot(IBootContext $context): void {
		$dispatcher = $context->getServerContainer()->get(IEventDispatcher::class);

		// ZGW object-lifecycle observer. Every handler in
		// ZaakRegisterEventListener opens by resolving the written object's
		// schema slug and comparing it against ZGWRegistryService's hardcoded
		// SCHEMAS map; the union of every slug any handler tests for is
		// declared here so an unrelated app's object write no longer
		// constructs the listener (nor its six injected services) at all.
		//
		// Registers are deliberately NOT declared: the listener never inspects
		// the register, and ZGWRegistryService's register slugs (zaken /
		// documenten / besluiten / catalogi) resolve to nothing on a stock
		// instance — declaring them would be an outage, not a narrowing.
		//
		// The in-handler `$slug === $this->registry->get*Schema()` guards stay
		// in place as defence in depth.
		foreach (self::OBJECT_EVENTS as $event) {
			$this->registerFilteredObjectListener(
				dispatcher: $dispatcher,
				event: $event,
				listener: \OCA\ZaakAfhandelApp\EventListener\ZaakRegisterEventListener::class,
				registers: null,
				schemas: self::ZGW_SCHEMAS
			);
		}
	}//end boot()
}//end class
