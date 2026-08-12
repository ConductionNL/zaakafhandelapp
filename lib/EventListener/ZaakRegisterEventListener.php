<?php

/**
 * ZaakAfhandelApp Event Listener
 *
 * @category  EventListener
 * @package   OCA\ZaakAfhandelApp\EventListener
 * @author    Conduction b.v. <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\EventListener;

use Exception;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectCreatingEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCA\OpenRegister\Event\ObjectUpdatingEvent;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\ZaakAfhandelApp\Service\ZGWZaakEventHandler;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Psr\Log\LoggerInterface;

/**
 * Event listener for handling ZaakAfhandelApp specific events.
 *
 * Deliberately thin: it decides which OpenRegister lifecycle moment fired and
 * how a failure is surfaced, and delegates every domain decision to
 * ZGWZaakEventHandler. Holding both halves here meant this class had to depend
 * on every ZGW service and every event class at once.
 */
class ZaakRegisterEventListener implements IEventListener {
	/**
	 * Constructor.
	 *
	 * @param ZGWZaakEventHandler $handler Handler that owns every ZGW domain decision.
	 * @param LoggerInterface $logger Logger used to surface non-validation handler failures.
	 */
	public function __construct(
		private readonly ZGWZaakEventHandler $handler,
		private readonly LoggerInterface $logger,
	) {
	}//end __construct()

	/**
	 * Dispatch an OpenRegister lifecycle event to the matching handler method.
	 *
	 * A CustomValidationException is rethrown so a rejected ZGW rule aborts the
	 * OpenRegister save; any other exception is logged and swallowed.
	 *
	 * @param Event $event The dispatched OpenRegister lifecycle event.
	 *
	 * @return void
	 *
	 * @throws CustomValidationException When a ZGW rule rejects the write.
	 */
	public function handle(Event $event): void {
		try {
			if ($event instanceof ObjectCreatedEvent) {
				$this->handler->onObjectCreated($event->getObject());
			} elseif ($event instanceof ObjectUpdatedEvent) {
				$this->handler->onObjectUpdated($event->getNewObject());
			} elseif ($event instanceof ObjectDeletedEvent) {
				$this->handler->onObjectDeleted($event->getObject());
			} elseif ($event instanceof ObjectCreatingEvent) {
				$this->handler->onObjectCreating($event->getObject());
			} elseif ($event instanceof ObjectUpdatingEvent) {
				$this->handler->onObjectUpdating($event->getNewObject());
			}
		} catch (CustomValidationException $e) {
			// A ZGW rule rejected the write; let it abort the OpenRegister save.
			throw $e;
		} catch (Exception $e) {
			$this->logError(event: $event, e: $e);
		}
	}//end handle()

	/**
	 * Log an error that occurred during event handling.
	 *
	 * @param Event $event The event being handled.
	 * @param Exception $e The exception raised by a handler.
	 *
	 * @return void
	 */
	private function logError(Event $event, Exception $e): void {
		try {
			$this->logger->error(
				'ZaakAfhandelApp: Error in event handler',
				[
					'eventType' => get_class($event),
					'exception' => $e->getMessage(),
				]
			);
		} catch (Exception $logException) {
			// Logging must never turn a handled error into a failed write, so a
			// broken log sink is swallowed here on purpose.
			unset($logException);
		}
	}//end logError()
}//end class
