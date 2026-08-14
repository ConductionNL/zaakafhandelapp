<?php

/**
 * ZaakAfhandelApp ZGW register-event handler.
 *
 * @category  Service
 * @package   OCA\ZaakAfhandelApp\Service
 * @author    Conduction b.v. <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\SchemaMapper;

/**
 * Routes an OpenRegister object lifecycle moment to the ZGW domain services.
 *
 * Split out of ZaakRegisterEventListener, which had to hold every ZGW service
 * plus every OpenRegister event class at once. The listener now owns only the
 * event plumbing (which event happened, and what to do when a handler throws);
 * this class owns the domain question - which schema was written, and which
 * ZGW rules that schema triggers.
 */
class ZGWZaakEventHandler {
	public function __construct(
		private readonly ZGWLogicService $logicService,
		private readonly ZGWZaakLifecycleService $lifecycleService,
		private readonly ZGWValidationService $validationService,
		private readonly ZGWZaakValidationService $caseValidator,
		private readonly ZaakTermijnService $termService,
		private readonly ZGWZaakOpschortingVerlengingService $suspensionService,
		private readonly ZGWRegistryService $registry,
		private readonly SchemaMapper $schemaMapper,
	) {
	}//end __construct()

	/**
	 * Apply the post-persist rules for a freshly created object.
	 *
	 * @param ObjectEntity $obj The persisted object.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function onObjectCreated(ObjectEntity $obj): void {
		$slug = $this->slugOf($obj);

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
	}//end onObjectCreated()

	/**
	 * Apply the post-persist rules for an updated object.
	 *
	 * @param ObjectEntity $obj The updated object.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function onObjectUpdated(ObjectEntity $obj): void {
		if ($this->slugOf($obj) === $this->registry->getZaakSchema()) {
			$this->lifecycleService->setVertrouwelijkheidaanduiding($obj);
		}
	}//end onObjectUpdated()

	/**
	 * Apply the cascade rules for a deleted object.
	 *
	 * @param ObjectEntity $obj The deleted object.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function onObjectDeleted(ObjectEntity $obj): void {
		$schema = $this->schemaMapper->find($obj->getSchema());
		$slug = $schema->getSlug();

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
	}//end onObjectDeleted()

	/**
	 * Apply the pre-persist rules for an object about to be created.
	 *
	 * @param ObjectEntity $obj The object about to be written.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function onObjectCreating(ObjectEntity $obj): void {
		$slug = $this->slugOf($obj);

		// Validate close prerequisites (resultaat, gebruiksrechten, date) before the status is
		// persisted. If any check fails, a CustomValidationException is thrown here and the status
		// write is aborted entirely — preventing an eindstatus from existing without the zaak's
		// archive metadata being set (H3 fix). The actual zaak mutations still happen in
		// onObjectCreated after successful persist.
		if ($slug === $this->registry->getStatusSchema()) {
			$this->lifecycleService->validateClosePrerequisites($obj);
		}

		if ($slug === $this->registry->getZaakSchema()) {
			$this->assertCaseWritable($obj);
			// Derive the behandeltermijn fields from the zaaktype before the zaak is
			// persisted (uiterlijkeEinddatumAfdoening from doorlooptijd, einddatumGepland
			// from servicenorm). Client-supplied dates are never overridden.
			$this->termService->deriveTermijnen($obj);
		}

		if ($slug === $this->registry->getBesluitSchema()) {
			$this->logicService->createZaakBesluit($obj);
		}

		if ($slug === $this->registry->getBioSchema()) {
			$this->validationService->validateBesluitInformatieObject($obj);
		}
	}//end onObjectCreating()

	/**
	 * Apply the pre-persist rules for an object about to be updated.
	 *
	 * @param ObjectEntity $obj The object about to be written.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-006
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-007
	 */
	public function onObjectUpdating(ObjectEntity $obj): void {
		if ($this->slugOf($obj) !== $this->registry->getZaakSchema()) {
			return;
		}

		$this->assertCaseWritable($obj);

		// Apply opschorting/verlenging transitions: gate on the zaaktype policy,
		// shift the termijn fields, and abort (via CustomValidationException) when
		// the transition is not allowed (ZRC opschorting/verlenging — Awb 4:14/4:15).
		$this->suspensionService->applyTransitions($obj);
	}//end onObjectUpdating()

	/**
	 * The zaak validations that gate both a create and an update write.
	 *
	 * @param ObjectEntity $obj The zaak about to be written.
	 *
	 * @return void
	 */
	private function assertCaseWritable(ObjectEntity $obj): void {
		$this->caseValidator->checkProductenOfDiensten($obj);
		$this->validationService->checkRelevanteAndereZaken($obj);
		$this->caseValidator->checkArchivePrerequisites($obj);
		$this->caseValidator->checkGegevensgroepen($obj);
	}//end assertZaakWritable()

	/**
	 * The slug of the schema an object belongs to.
	 *
	 * @param ObjectEntity $obj The object.
	 *
	 * @return string The schema slug.
	 */
	private function slugOf(ObjectEntity $obj): string {
		return (string)$this->schemaMapper->find($obj->getSchema())->getSlug();
	}//end slugOf()
}//end class
