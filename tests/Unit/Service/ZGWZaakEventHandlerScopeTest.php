<?php

/**
 * Unit tests for ZGWZaakEventHandler's register scoping.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Service
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\Register;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\ZaakAfhandelApp\Service\ZaakTermijnService;
use OCA\ZaakAfhandelApp\Service\ZGWLogicService;
use OCA\ZaakAfhandelApp\Service\ZGWObjectScopeService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use OCA\ZaakAfhandelApp\Service\ZGWValidationService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakEventHandler;
use OCA\ZaakAfhandelApp\Service\ZGWZaakLifecycleService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakOpschortingVerlengingService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakValidationService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * REGRESSION: a ZGW schema slug is not proof the object is ours.
 *
 * dossiq ships its own `zaakinformatieobject`, `besluitinformatieobject` and
 * `zaaktypeInformatieobjecttype` schemas in its own `dossiq` register, with
 * `case` where ZGW has `zaak`. Dispatching on the slug alone ran this app's ZGW
 * cascade against dossiq's payload, and `ZGWLogicService::createOio()`'s typed
 * `string $objectUrl` parameter turned the missing `zaak` into a TypeError — an
 * unexplained HTTP 500 on every dossiq zaakinformatieobject write.
 *
 * These lock every one of the five handler entry points: an object whose
 * register carries another app's application stamp must reach no ZGW service.
 */
class ZGWZaakEventHandlerScopeTest extends TestCase {

	/**
	 * @var ZGWLogicService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $logicService;

	/**
	 * @var ZGWZaakLifecycleService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $lifecycleService;

	/**
	 * @var ZGWValidationService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $validationService;

	/**
	 * @var ZGWZaakValidationService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $caseValidator;

	/**
	 * @var ZaakTermijnService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $termService;

	/**
	 * @var ZGWZaakOpschortingVerlengingService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $suspensionService;

	/**
	 * @var SchemaMapper&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $schemaMapper;

	/**
	 * @var RegisterMapper&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $registerMapper;

	/**
	 * @var ZGWZaakEventHandler
	 */
	private $handler;

	/**
	 * Wire the handler with mocked collaborators and a real scope resolver.
	 *
	 * The registry and the scope resolver are deliberately REAL: the ownership
	 * decision is exactly what they encode, and mocking them would let the test
	 * pass against a handler that never asks the question.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->logicService = $this->createMock(ZGWLogicService::class);
		$this->lifecycleService = $this->createMock(ZGWZaakLifecycleService::class);
		$this->validationService = $this->createMock(ZGWValidationService::class);
		$this->caseValidator = $this->createMock(ZGWZaakValidationService::class);
		$this->termService = $this->createMock(ZaakTermijnService::class);
		$this->suspensionService = $this->createMock(ZGWZaakOpschortingVerlengingService::class);
		$this->schemaMapper = $this->createMock(SchemaMapper::class);
		$this->registerMapper = $this->createMock(RegisterMapper::class);

		$this->handler = new ZGWZaakEventHandler(
			$this->logicService,
			$this->lifecycleService,
			$this->validationService,
			$this->caseValidator,
			$this->termService,
			$this->suspensionService,
			new ZGWRegistryService(),
			$this->schemaMapper,
			new ZGWObjectScopeService(
				new ZGWRegistryService(),
				$this->schemaMapper,
				$this->registerMapper,
				$this->createMock(LoggerInterface::class)
			)
		);
	}//end setUp()

	/**
	 * Stage an object in a register/schema pair with the given ownership stamps.
	 *
	 * @param string $schemaSlug The schema slug the object carries.
	 * @param string $registerSlug The slug of the register it lives in.
	 * @param string|null $application The app that provisioned both, or null.
	 *
	 * @return ObjectEntity
	 */
	private function stage(string $schemaSlug, string $registerSlug, ?string $application): ObjectEntity {
		$schema = new Schema();
		$schema->setSlug($schemaSlug);
		$schema->setApplication($application);

		$register = new Register();
		$register->setSlug($registerSlug);
		$register->setApplication($application);

		$this->schemaMapper->method('find')->willReturn($schema);
		$this->registerMapper->method('find')->willReturn($register);

		$object = new ObjectEntity();
		$object->setUuid('003d39b9-49ab-4000-8000-000000000000');
		$object->setRegister('23');
		$object->setSchema('309');
		$object->setObject(['case' => 'http://example/case/1', 'informatieobject' => 'http://example/io/1']);

		return $object;
	}//end stage()

	/**
	 * Assert that no ZGW service is reached at all.
	 *
	 * @return void
	 */
	private function expectNoZgwRules(): void {
		$this->logicService->expects($this->never())->method($this->anything());
		$this->lifecycleService->expects($this->never())->method($this->anything());
		$this->validationService->expects($this->never())->method($this->anything());
		$this->caseValidator->expects($this->never())->method($this->anything());
		$this->termService->expects($this->never())->method($this->anything());
		$this->suspensionService->expects($this->never())->method($this->anything());
	}//end expectNoZgwRules()

	/**
	 * onObjectCreated() must not run the OIO cascade on another app's zaakinformatieobject.
	 *
	 * This is the exact write that 500'd: dossiq's `zaakinformatieobject`, in the
	 * `dossiq` register, with `case` instead of `zaak`.
	 *
	 * @return void
	 */
	public function testCreatedSkipsForeignZaakInformatieObject(): void {
		$this->expectNoZgwRules();
		$this->handler->onObjectCreated($this->stage('zaakinformatieobject', 'dossiq', 'dossiq'));
		$this->addToAssertionCount(1);
	}//end testCreatedSkipsForeignZaakInformatieObject()

	/**
	 * onObjectCreating() must not validate another app's besluitinformatieobject.
	 *
	 * @return void
	 */
	public function testCreatingSkipsForeignBesluitInformatieObject(): void {
		$this->expectNoZgwRules();
		$this->handler->onObjectCreating($this->stage('besluitinformatieobject', 'dossiq', 'dossiq'));
		$this->addToAssertionCount(1);
	}//end testCreatingSkipsForeignBesluitInformatieObject()

	/**
	 * onObjectCreating() must not run the zaak rules on another app's `zaak`.
	 *
	 * `zaak` is an ordinary domain word and is at least as likely to collide as
	 * `zaakinformatieobject`.
	 *
	 * @return void
	 */
	public function testCreatingSkipsForeignZaak(): void {
		$this->expectNoZgwRules();
		$this->handler->onObjectCreating($this->stage('zaak', 'someotherapp', 'someotherapp'));
		$this->addToAssertionCount(1);
	}//end testCreatingSkipsForeignZaak()

	/**
	 * onObjectUpdating() must not run the opschorting rules on another app's `zaak`.
	 *
	 * @return void
	 */
	public function testUpdatingSkipsForeignZaak(): void {
		$this->expectNoZgwRules();
		$this->handler->onObjectUpdating($this->stage('zaak', 'someotherapp', 'someotherapp'));
		$this->addToAssertionCount(1);
	}//end testUpdatingSkipsForeignZaak()

	/**
	 * onObjectUpdated() must not set vertrouwelijkheidaanduiding on another app's `zaak`.
	 *
	 * @return void
	 */
	public function testUpdatedSkipsForeignZaak(): void {
		$this->expectNoZgwRules();
		$this->handler->onObjectUpdated($this->stage('zaak', 'someotherapp', 'someotherapp'));
		$this->addToAssertionCount(1);
	}//end testUpdatedSkipsForeignZaak()

	/**
	 * onObjectDeleted() must not cascade a delete into another app's register.
	 *
	 * @return void
	 */
	public function testDeletedSkipsForeignZaakInformatieObject(): void {
		$this->expectNoZgwRules();
		$this->handler->onObjectDeleted($this->stage('zaakinformatieobject', 'dossiq', 'dossiq'));
		$this->addToAssertionCount(1);
	}//end testDeletedSkipsForeignZaakInformatieObject()

	/**
	 * A `status` write in the canonical ZGW `zaken` register still dispatches.
	 *
	 * The narrowing must not take a working ZGW deployment dark, so this is the
	 * other half of the guard: same slug, this app's register, rules DO run.
	 *
	 * @return void
	 */
	public function testCreatedStillDispatchesInAZgwRegister(): void {
		$object = $this->stage('status', 'zaken', 'someotherapp');

		$this->lifecycleService->expects($this->once())->method('closeZaak')->with($object);
		$this->lifecycleService->expects($this->once())->method('reopenZaak')->with($object);

		$this->handler->onObjectCreated($object);
	}//end testCreatedStillDispatchesInAZgwRegister()

	/**
	 * An unstamped register — one an administrator created by hand — still dispatches.
	 *
	 * Most instances provision the ZGW registers manually, so they carry no
	 * application at all. Treating an absent stamp as foreign would be an outage.
	 *
	 * @return void
	 */
	public function testCreatedStillDispatchesWhenOwnershipIsUnrecorded(): void {
		$object = $this->stage('status', 'zaakafhandelapp-handmade', null);

		$this->lifecycleService->expects($this->once())->method('closeZaak')->with($object);
		$this->lifecycleService->expects($this->once())->method('reopenZaak')->with($object);

		$this->handler->onObjectCreated($object);
	}//end testCreatedStillDispatchesWhenOwnershipIsUnrecorded()
}//end class
