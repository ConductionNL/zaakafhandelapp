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

use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Event\ObjectCreatingEvent;
use OCA\OpenRegister\Event\ObjectUpdatingEvent;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\ZaakAfhandelApp\Service\ZGWLogicService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use OCA\ZaakAfhandelApp\Service\ZGWValidationService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakLifecycleService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakOpschortingVerlengingService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakValidationService;
use OCA\ZaakAfhandelApp\Service\ZaakTermijnService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\OpenRegister\Event\ObjectCreatedEvent;
use OCA\OpenRegister\Event\ObjectUpdatedEvent;
use OCA\OpenRegister\Event\ObjectDeletedEvent;
use Psr\Log\LoggerInterface;

/**
 * Event listener for handling ZaakAfhandelApp specific events.
 *
 * Exceeds PHPMD's object-coupling threshold (18 vs 13): this is the single
 * fan-out point for every OpenRegister zaak event, so it names one service per
 * ZGW rule family. Splitting it would need the same services in each part.
 *
 * The two constructor properties over PHPMD's 20-character variable limit
 * ($zaakValidationService, $opschortingVerlengingService) are named after the
 * service classes they hold; shortening them would obscure the wiring, and the
 * ZGW domain vocabulary is simply longer than the rule's default.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ZaakRegisterEventListener implements IEventListener
{
    public function __construct(
        private readonly ZGWLogicService $logicService,
        private readonly ZGWZaakLifecycleService $lifecycleService,
        private readonly ZGWValidationService $validationService,
        private readonly ZGWZaakValidationService $zaakValidationService,
        private readonly ZaakTermijnService $termijnService,
        private readonly ZGWZaakOpschortingVerlengingService $opschortingVerlengingService,
        private readonly ZGWRegistryService $registry,
        private readonly SchemaMapper $schemaMapper,
    ) {
    }//end __construct()

    public function handle(Event $event): void
    {
        try {
            if ($event instanceof ObjectCreatedEvent) {
                $this->handleObjectCreated($event);
            } else if ($event instanceof ObjectUpdatedEvent) {
                $this->handleObjectUpdated($event);
            } else if ($event instanceof ObjectDeletedEvent) {
                $this->handleObjectDeleted($event);
            } else if ($event instanceof ObjectCreatingEvent) {
                $this->handleObjectCreating($event);
            } else if ($event instanceof ObjectUpdatingEvent) {
                $this->handleObjectUpdating($event);
            }
        } catch (CustomValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError($event, $e);
        }
    }//end handle()

    private function handleObjectCreated(ObjectCreatedEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getObject()->getSchema())->getSlug();
        $obj  = $event->getObject();

        if ($slug === $this->registry->getStatusSchema()) {
            // Re-open or close the zaak now that the status record is confirmed persisted.
            // closeZaak MUST run in ObjectCreated (not ObjectCreating) so the zaak is only
            // mutated when the triggering status write is known-successful — fixes #274.
            //
            // No double-dispatch risk (M4): closeZaak and reopenZaak are mutually exclusive
            // via their isEindStatus() guard. closeZaak is a no-op when the status is not an
            // eindstatus; reopenZaak is a no-op when the status IS an eindstatus. Both methods
            // check isEindStatus independently, so only one path executes per status event.
            $this->lifecycleService->closeZaak($obj);
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

        if ($slug === $this->registry->getZTIOTSchema()) {
            $this->logicService->createZaakTypeInformatieObjecttype($obj);
        }

    }//end handleObjectCreated()

    private function handleObjectUpdated(ObjectUpdatedEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getNewObject()->getSchema())->getSlug();

        if ($slug === $this->registry->getZaakSchema()) {
            $this->lifecycleService->setVertrouwelijkheidaanduiding($event->getNewObject());
        }
    }//end handleObjectUpdated()

    private function handleObjectDeleted(ObjectDeletedEvent $event): void
    {
        $schema = $this->schemaMapper->find($event->getObject()->getSchema());
        $slug   = $schema->getSlug();
        $obj    = $event->getObject();

        if ($slug === $this->registry->getZioSchema() || $slug === $this->registry->getBioSchema()) {
            $this->logicService->deleteObjectInformatieObject($obj, $schema);
        }

        if ($slug === $this->registry->getZaakSchema()) {
            $this->lifecycleService->deleteZaak($obj);
        }

        if ($slug === $this->registry->getBesluitSchema()) {
            $this->logicService->deleteBesluit($obj);
        }

        if ($slug === $this->registry->getZTIOTSchema()) {
            $this->logicService->deleteZaakTypeInformatieObjecttype($obj);
        }
    }//end handleObjectDeleted()

    private function handleObjectCreating(ObjectCreatingEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getObject()->getSchema())->getSlug();
        $obj  = $event->getObject();

        // Validate close prerequisites (resultaat, gebruiksrechten, date) before the status is
        // persisted. If any check fails, a CustomValidationException is thrown here and the status
        // write is aborted entirely — preventing an eindstatus from existing without the zaak's
        // archive metadata being set (H3 fix). The actual zaak mutations still happen in
        // handleObjectCreated after successful persist.
        if ($slug === $this->registry->getStatusSchema()) {
            $this->lifecycleService->validateClosePrerequisites($obj);
        }

        if ($slug === $this->registry->getZaakSchema()) {
            $this->zaakValidationService->checkProductenOfDiensten($obj);
            $this->validationService->checkRelevanteAndereZaken($obj);
            $this->zaakValidationService->checkArchivePrerequisites($obj);
            $this->zaakValidationService->checkGegevensgroepen($obj);
            // Derive the behandeltermijn fields from the zaaktype before the zaak is
            // persisted (uiterlijkeEinddatumAfdoening from doorlooptijd, einddatumGepland
            // from servicenorm). Client-supplied dates are never overridden.
            $this->termijnService->deriveTermijnen($obj);
        }

        if ($slug === $this->registry->getBesluitSchema()) {
            $this->logicService->createZaakBesluit($obj);
        }

        if ($slug === $this->registry->getBioSchema()) {
            $this->validationService->validateBesluitInformatieObject($obj);
        }
    }//end handleObjectCreating()

    private function handleObjectUpdating(ObjectUpdatingEvent $event): void
    {
        $slug = $this->schemaMapper->find($event->getNewObject()->getSchema())->getSlug();

        if ($slug === $this->registry->getZaakSchema()) {
            $obj = $event->getNewObject();
            $this->zaakValidationService->checkProductenOfDiensten($obj);
            $this->validationService->checkRelevanteAndereZaken($obj);
            $this->zaakValidationService->checkArchivePrerequisites($obj);
            $this->zaakValidationService->checkGegevensgroepen($obj);
            // Apply opschorting/verlenging transitions: gate on the zaaktype policy,
            // shift the termijn fields, and abort (via CustomValidationException) when
            // the transition is not allowed (ZRC opschorting/verlenging — Awb 4:14/4:15).
            $this->opschortingVerlengingService->applyTransitions($obj);
        }
    }//end handleObjectUpdating()

    /**
     * Log an error that occurred during event handling.
     *
     * @psalm-suppress UnusedParam — $event and $e are consumed via get_class()/$e->getMessage();
     *                 Psalm cannot trace usage through \OC::$server->get() (OC is an env stub).
     */
    private function logError(Event $event, \Exception $e): void
    {
        try {
            $logger = \OC::$server->get(LoggerInterface::class);
            $logger->error(
                    'ZaakAfhandelApp: Error in event handler',
                    [
                        'eventType' => get_class($event),
                        'exception' => $e->getMessage(),
                    ]
                    );
        } catch (\Exception $logException) {
            // Silently fail
        }
    }//end logError()
}//end class
