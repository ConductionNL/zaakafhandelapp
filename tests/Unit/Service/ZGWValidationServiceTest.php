<?php

/**
 * Unit tests for ZGWValidationService.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Service
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\OpenRegister\Service\ObjectService;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCA\ZaakAfhandelApp\Service\ZGWValidationService;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit\Framework\TestCase;

/**
 * Tests ZGWValidationService — cross-object reference validation.
 *
 * Locks: relevanteAndereZaken null/missing-url handling + DoesNotExist mapping;
 * besluitInformatieObject type validation; the findByUrl() helper drives the
 * named-parameter find(_extend:) API.
 */
class ZGWValidationServiceTest extends TestCase {

	/**
	 * @var ObjectService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $objectService;

	/**
	 * @var ZGWValidationService
	 */
	private $service;

	/**
	 * Wire the service with a mocked ObjectService.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);

		$mapperService = $this->createMock(ObjectMapperService::class);
		$mapperService->method('getOpenRegisters')->willReturn($this->objectService);

		$this->service = new ZGWValidationService($mapperService);
	}//end setUp()

	/**
	 * Build a real ObjectEntity carrying the given payload.
	 *
	 * @param array<string,mixed> $data The object payload.
	 *
	 * @return ObjectEntity
	 */
	private function entity(array $data): ObjectEntity {
		$entity = new ObjectEntity();
		$entity->setObject($data);
		return $entity;
	}//end entity()

	/**
	 * checkRelevanteAndereZaken() with a non-array relevanteAndereZaken is a
	 * no-op (never resolves anything).
	 *
	 * @return void
	 */
	public function testCheckRelevanteAndereZakenNonArrayIsNoop(): void {
		$this->objectService->expects($this->never())->method('find');

		$this->service->checkRelevanteAndereZaken($this->entity(['relevanteAndereZaken' => null]));
		$this->addToAssertionCount(1);
	}//end testCheckRelevanteAndereZakenNonArrayIsNoop()

	/**
	 * An entry without a url is skipped (never resolved) but does not fail the
	 * whole check.
	 *
	 * @return void
	 */
	public function testCheckRelevanteAndereZakenSkipsEntryWithoutUrl(): void {
		$this->objectService->expects($this->never())->method('find');

		$this->service->checkRelevanteAndereZaken(
			$this->entity(['relevanteAndereZaken' => [['aardRelatie' => 'vervolg']]])
		);
		$this->addToAssertionCount(1);
	}//end testCheckRelevanteAndereZakenSkipsEntryWithoutUrl()

	/**
	 * A relevante zaak whose url resolves successfully passes.
	 *
	 * @return void
	 */
	public function testCheckRelevanteAndereZakenResolvableUrlPasses(): void {
		$this->objectService->method('find')->willReturn($this->entity(['identificatie' => 'ZAAK-X']));

		$this->service->checkRelevanteAndereZaken(
			$this->entity(['relevanteAndereZaken' => [['url' => 'http://example/zaak/99']]])
		);
		$this->addToAssertionCount(1);
	}//end testCheckRelevanteAndereZakenResolvableUrlPasses()

	/**
	 * A relevante zaak whose url does not resolve (DoesNotExistException) is
	 * mapped to a CustomValidationException.
	 *
	 * @return void
	 */
	public function testCheckRelevanteAndereZakenUnresolvableUrlRejects(): void {
		$this->objectService->method('find')->willThrowException(new DoesNotExistException('gone'));

		$this->expectException(CustomValidationException::class);
		$this->service->checkRelevanteAndereZaken(
			$this->entity(['relevanteAndereZaken' => [['url' => 'http://example/zaak/missing']]])
		);
	}//end testCheckRelevanteAndereZakenUnresolvableUrlRejects()

	/**
	 * validateBesluitInformatieObject() passes when the informatieobject's type
	 * omschrijving is present on the besluittype. The findByUrl() helper passes
	 * the resolved id + _extend through the named-parameter API.
	 *
	 * @return void
	 */
	public function testValidateBesluitInformatieObjectAllowedTypePasses(): void {
		$eio = $this->entity(['informatieobjecttype' => ['omschrijving' => 'factuur']]);
		$decision = $this->entity(['besluittype' => ['informatieobjecttypen' => ['factuur', 'brief']]]);

		$this->objectService->method('find')->willReturnCallback(
			function ($id, $_extend = []) use ($eio, $decision) {
				return in_array('informatieobjecttype', (array)$_extend, true) ? $eio : $decision;
			}
		);

		$this->service->validateBesluitInformatieObject(
			$this->entity(
				[
					'informatieobject' => 'http://example/eio/1',
					'besluit' => 'http://example/besluit/1',
				]
			)
		);
		$this->addToAssertionCount(1);
	}//end testValidateBesluitInformatieObjectAllowedTypePasses()

	/**
	 * validateBesluitInformatieObject() rejects when the informatieobjecttype is
	 * not present on the besluittype.
	 *
	 * @return void
	 */
	public function testValidateBesluitInformatieObjectDisallowedTypeRejects(): void {
		$eio = $this->entity(['informatieobjecttype' => ['omschrijving' => 'geheim']]);
		$decision = $this->entity(['besluittype' => ['informatieobjecttypen' => ['factuur', 'brief']]]);

		$this->objectService->method('find')->willReturnCallback(
			function ($id, $_extend = []) use ($eio, $decision) {
				return in_array('informatieobjecttype', (array)$_extend, true) ? $eio : $decision;
			}
		);

		$this->expectException(CustomValidationException::class);
		$this->service->validateBesluitInformatieObject(
			$this->entity(
				[
					'informatieobject' => 'http://example/eio/1',
					'besluit' => 'http://example/besluit/1',
				]
			)
		);
	}//end testValidateBesluitInformatieObjectDisallowedTypeRejects()
}//end class
