<?php
/**
 * ZaakAfhandelApp Event Listener
 *
 * @category  EventListener
 * @package   OCA\ZaakAfhandelApp\EventListener
 * @author    Conduction b.v. <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   AGPL-3.0-or-later https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\EventListener;

use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Event\ObjectCreatingEvent;
use OCA\OpenRegister\Event\ObjectUpdatingEvent;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\ZaakAfhandelApp\Service\ZGWLogicService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use OCA\ZaakAfhandelApp\Service\ZGWValidationService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakLifecycleService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakValidationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use Psr\Log\LoggerInterface;

/**
 * Event listener for handling ZaakAfhandelApp specific events.
 */
class ZaakRegisterEventListener implements IEventListener
{

    private const EVENT_HANDLERS = [
        ObjectCreatedEvent::class => 'handleObjectCreated',
        ObjectUpdatedEvent::class => 'handleObjectUpdated',
        ObjectDeletedEvent::class => 'handleObjectDeleted',
        ObjectCreatingEvent::class => 'handleObjectCreating',
        ObjectUpdatingEvent::class => 'handleObjectUpdating',
    ];

    public function __construct(
        private readonly ZGWLogicService $logicService,
        private readonly ZGWZaakLifecycleService $lifecycleService,
        private readonly ZGWValidationService $validationService,
        private readonly ZGWZaakValidationService $zaakValidationService,
        private readonly ZGWRegistryService $registry,
        private readonly SchemaMapper $schemaMapper,
    ) {
    }

    public function handle(Event $event): void
    {
        try {
            $handler = self::EVENT_HANDLERS[get_class($event)] ?? null;
            if ($handler !== null) {
                $this->$handler($event);
            }
        } catch (CustomValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError($event, $e);
        }
    }

    private function handleObjectCreated(ObjectCreatedEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getObject()->getSchema())->getSlug();
        $obj = $event->getObject();

        if ($slug === $this->registry->getStatusSchema()) {
            $this->lifecycleService->reopenZaak($obj);
        }
        if ($slug === $this->registry->getZioSchema()) {
            $this->logicService->createObjectInformatieObjectZaak($obj);
        }
        if ($slug === $this->registry->getBioSchema()) {
            $this->logicService->createObjectInformatieObjectBesluit($obj);
        }
        if ($slug === $this->registry->getZaakSchema()) {
            $this->lifecycleService->setVertrouwelijkheidaanduiding($obj);
        }
    }

    private function handleObjectUpdated(ObjectUpdatedEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getNewObject()->getSchema())->getSlug();

        if ($slug === $this->registry->getZaakSchema()) {
            $this->lifecycleService->setVertrouwelijkheidaanduiding($event->getNewObject());
        }
    }

    private function handleObjectDeleted(ObjectDeletedEvent $event): void
    {
        $schema = $this->schemaMapper->find($event->getObject()->getSchema());
        $slug = $schema->getSlug();
        $obj = $event->getObject();

        if ($slug === $this->registry->getZioSchema() || $slug === $this->registry->getBioSchema()) {
            $this->logicService->deleteObjectInformatieObject($obj, $schema);
        }
        if ($slug === $this->registry->getZaakSchema()) {
            $this->lifecycleService->deleteZaak($obj);
        }
        if ($slug === $this->registry->getBesluitSchema()) {
            $this->logicService->deleteBesluit($obj);
        }
    }

    private function handleObjectCreating(ObjectCreatingEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getObject()->getSchema())->getSlug();
        $obj = $event->getObject();

        if ($slug === $this->registry->getStatusSchema()) {
            $this->lifecycleService->closeZaak($obj);
        }
        if ($slug === $this->registry->getZaakSchema()) {
            $this->zaakValidationService->checkProductenOfDiensten($obj);
            $this->validationService->checkRelevanteAndereZaken($obj);
            $this->zaakValidationService->checkArchivePrerequisites($obj);
            $this->zaakValidationService->checkGegevensgroepen($obj);
        }
        if ($slug === $this->registry->getBesluitSchema()) {
            $this->logicService->createZaakBesluit($obj);
        }
        if ($slug === $this->registry->getBioSchema()) {
            $this->validationService->validateBesluitInformatieObject($obj);
        }
    }

    private function handleObjectUpdating(ObjectUpdatingEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getNewObject()->getSchema())->getSlug();

        if ($slug === $this->registry->getZaakSchema()) {
            $obj = $event->getNewObject();
            $this->zaakValidationService->checkProductenOfDiensten($obj);
            $this->validationService->checkRelevanteAndereZaken($obj);
            $this->zaakValidationService->checkArchivePrerequisites($obj);
            $this->zaakValidationService->checkGegevensgroepen($obj);
        }
    }

    private function logError(Event $event, \Exception $e): void
    {
        try {
            $logger = \OC::$server->get(LoggerInterface::class);
            $logger->error('ZaakAfhandelApp: Error in event handler', [
                'eventType' => get_class($event),
                'exception' => $e->getMessage(),
            ]);
        } catch (\Exception $logException) {
            // Silently fail
        }
    }
}
