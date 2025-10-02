<?php
/**
 * ZaakAfhandelApp Event Listener
 *
 * This file contains the listener class for handling events from OpenRegister
 * specific to the ZaakAfhandelApp application.
 *
 * @category  EventListener
 * @package   OCA\ZaakAfhandelApp\EventListener
 * @author    Conduction b.v. <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @version   1.0.0
 * @link      https://github.com/ConductionNL/OpenConnector
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\EventListener;

use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Event\ObjectCreatingEvent;
use OCA\OpenRegister\Event\ObjectUpdatingEvent;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\ZaakAfhandelApp\Service\ContactpersoonService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCA\ZaakAfhandelApp\Service\SettingsService;
use OCA\ZaakAfhandelApp\Service\GebruikSyncService;
use OCA\ZaakAfhandelApp\Service\ZGWLogicService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use OCA\OpenRegister\Event\ObjectLockedEvent;
use OCA\OpenRegister\Event\ObjectUnlockedEvent;
use OCA\OpenRegister\Event\ObjectRevertedEvent;
use Psr\Log\LoggerInterface;

/**
 * Event listener for handling software catalog specific events.
 *
 * This listener handles organization, contact, and user (gebruiker) related events
 * in the software catalog, including user management, email notifications, and
 * user blocking/unblocking functionality.
 *
 * @category EventListener
 * @package  OCA\ZaakAfhandelApp\EventListener
 * @author   Conduction b.v. <info@conduction.nl>
 * @license  AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 * @version  1.0.0
 * @link     https://github.com/ConductionNL/OpenConnector
 * @todo     This listener should be moved to the software catalog app
 */
class ZaakRegisterEventListener implements IEventListener
{


    /**
     * Constructor for ZaakAfhandelAppEventListener
     */
    public function __construct(
        private readonly ZGWLogicService $logicService,
        private readonly SchemaMapper $schemaMapper,
    ) {
    }

    /**
     * Handles events related to software catalog objects
     *
     * DISABLED: All processing is now handled by cron-based OrganizationSyncService
     * to avoid race conditions and ensure consistent processing.
     *
     * @param  Event $event The event to handle
     *
     * @return void
     */
    public function handle(Event $event): void
    {
        try {
            $logger = \OC::$server->get(LoggerInterface::class);

            $logger->debug('ZaakAfhandelApp: Processing event', [
                'eventType' => get_class($event),
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            if ($event instanceof ObjectCreatedEvent) {
                $this->handleObjectCreated($event, $logger);
            } elseif ($event instanceof ObjectUpdatedEvent) {
                $this->handleObjectUpdated($event, $logger);
            } elseif ($event instanceof ObjectDeletedEvent) {
                $this->handleObjectDeleted($event, $logger);
            } elseif ($event instanceof ObjectCreatingEvent) {
                $this->handleObjectCreating($event, $logger);
            } elseif ($event instanceof ObjectUpdatingEvent) {
                $this->handleObjectUpdating($event, $logger);
            } elseif ($event instanceof ObjectCreatingEvent) {
                $this->handleObjectDeleting($event, $logger);
            } elseif ($event instanceof ObjectLockedEvent || $event instanceof ObjectUnlockedEvent || $event instanceof ObjectRevertedEvent) {
                $logger->debug('ZaakAfhandelApp: Ignoring object lifecycle event', [
                    'eventType' => get_class($event)
                ]);
            } else {
                $logger->debug('ZaakAfhandelApp: Unknown event type ignored', [
                    'eventType' => get_class($event)
                ]);
            }
        } catch (CustomValidationException $e) {
            // These errors should not be surpressed
            throw $e;
        } catch (\Exception $e) {
            try {
                $logger = \OC::$server->get(LoggerInterface::class);
                $logger->error('ZaakAfhandelApp: Error in event handler', [
                    'eventType' => get_class($event),
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            } catch (\Exception $logException) {
                // Silently fail if logging fails - better than breaking the event system
            }
        }
    }



    /**
     * Handles object creation events
     *
     * @param ObjectCreatedEvent $event The creation event
     * @param ContactpersoonService $contactpersoonService The contact person service
     * @param SettingsService $settingsService The settings service
     * @param LoggerInterface $logger The logger instance
     * @return void
     */
    private function handleObjectCreated(ObjectCreatedEvent $event, LoggerInterface $logger): void
    {
        $schema = $event->getObject()->getSchema();
        $schema = $this->schemaMapper->find($schema);

        if ($schema->getSlug() === $this->logicService->getStatusSchema()) {
            $this->logicService->closeZaak($event->getObject());
            $this->logicService->reopenZaak($event->getObject());
        }

        if ($schema->getSlug() === $this->logicService->getZioSchema()) {
            $this->logicService->createObjectInformatieObjectZaak($event->getObject());
        }

        if ($schema->getSlug() === $this->logicService->getBioSchema()) {
            $this->logicService->createObjectInformatieObjectBesluit($event->getObject());
        }

        if ($schema->getSlug() === $this->logicService->getZaakSchema()) {
            $this->logicService->setVertrouwelijkheidaanduiding($event->getObject());
        }

    }

    /**
     * Handles object update events
     *
     * @param ObjectUpdatedEvent $event The update event
     * @param ContactpersoonService $contactpersoonService The contact person service
     * @param SettingsService $settingsService The settings service
     * @param LoggerInterface $logger The logger instance
     * @return void
     */
    private function handleObjectUpdated(ObjectUpdatedEvent $event, LoggerInterface $logger): void
    {
        $schema = $event->getNewObject()->getSchema();
        $schema = $this->schemaMapper->find($schema);

        if ($schema->getSlug() === $this->logicService->getZaakSchema()) {
            $this->logicService->setVertrouwelijkheidaanduiding($event->getNewObject());
        }

    }

    /**
     * Handles object deletion events
     *
     * @param ObjectDeletedEvent $event The deletion event
     * @param ContactpersoonService $contactpersoonService The contact person service
     * @param SettingsService $settingsService The settings service
     * @param LoggerInterface $logger The logger instance
     * @return void
     */
    private function handleObjectDeleted(ObjectDeletedEvent $event, LoggerInterface $logger): void
    {
        $schema = $event->getObject()->getSchema();
        $schema = $this->schemaMapper->find($schema);

        if ($schema->getSlug() === $this->logicService->getZioSchema()
            || $schema->getSlug() === $this->logicService->getBioSchema()
        ) {
            $this->logicService->deleteObjectInformatieObject($event->getObject(), $schema);
        }

        if ($schema->getSlug() === $this->logicService->getZaakSchema()
        ) {
            $this->logicService->deleteZaak($event->getObject());
        }
    }

    /**
     * Handles object locking events
     *
     * @param ObjectLockedEvent $event The locking event
     * @param ZaakAfhandelAppueService $softwareCatalogueService The software catalog service
     * @param SettingsService $settingsService The settings service
     * @param LoggerInterface $logger The logger instance
     * @return void
     */
    private function handleObjectLocked(ObjectLockedEvent $event, ZaakAfhandelAppueService $softwareCatalogueService, SettingsService $settingsService, LoggerInterface $logger): void
    {

    }

    /**
     * Handles object unlocking events
     *
     * @param ObjectUnlockedEvent $event The unlocking event
     * @param ZaakAfhandelAppueService $softwareCatalogueService The software catalog service
     * @param SettingsService $settingsService The settings service
     * @param LoggerInterface $logger The logger instance
     * @return void
     */
    private function handleObjectUnlocked(ObjectUnlockedEvent $event, ZaakAfhandelAppueService $softwareCatalogueService, SettingsService $settingsService, LoggerInterface $logger): void
    {

    }

    /**
     * Handles object reversion events
     *
     * @param ObjectRevertedEvent $event The reversion event
     * @param ZaakAfhandelAppueService $softwareCatalogueService The software catalog service
     * @param SettingsService $settingsService The settings service
     * @param LoggerInterface $logger The logger instance
     * @return void
     */
    private function handleObjectReverted(ObjectRevertedEvent $event, ZaakAfhandelAppueService $softwareCatalogueService, SettingsService $settingsService, LoggerInterface $logger): void
    {

    }

    private function handleObjectCreating(ObjectCreatingEvent $event, mixed $logger)
    {
        $schema = $event->getObject()->getSchema();
        $schema = $this->schemaMapper->find($schema);

        if ($schema->getSlug() === $this->logicService->getZaakSchema()
        ) {
            $this->logicService->checkProductenOfDiensten($event->getObject());
            $this->logicService->checkRelevanteAndereZaken($event->getObject());
            $this->logicService->checkArchivePrerequisites($event->getObject());
        }
    }

    private function handleObjectUpdating(ObjectUpdatingEvent $event, mixed $logger)
    {
        $schema = $event->getNewObject()->getSchema();
        $schema = $this->schemaMapper->find($schema);

        if ($schema->getSlug() === $this->logicService->getZaakSchema()
        ) {
            $this->logicService->checkProductenOfDiensten($event->getNewObject());
            $this->logicService->checkRelevanteAndereZaken($event->getNewObject());
            $this->logicService->checkArchivePrerequisites($event->getNewObject());
        }
    }

    private function handleObjectDeleting(ObjectCreatingEvent $event, mixed $logger)
    {
    }
}
