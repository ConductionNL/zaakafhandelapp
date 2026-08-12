<?php

/**
 * Unit tests for ZGWZaakCloseService.
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
use OCA\ZaakAfhandelApp\Service\ZGWArchiveDateService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakCloseService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests ZGWZaakCloseService — eindstatus detection + close prerequisites.
 *
 * Locks: isEindStatus() resolves the statustype via the named-parameter
 * find(_extend:) API and compares volgnummer to the max; close prerequisites
 * reject a missing resultaat and an invalid datumStatusGezet.
 */
class ZGWZaakCloseServiceTest extends TestCase {

	/**
	 * @var ObjectService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $objectService;

	/**
	 * @var ZGWRegistryService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $registry;

	/**
	 * @var ZGWZaakCloseService
	 */
	private $service;

	/**
	 * Wire the service with mocked collaborators.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);
		$this->registry = $this->createMock(ZGWRegistryService::class);

		$mapperService = $this->createMock(ObjectMapperService::class);
		$mapperService->method('getOpenRegisters')->willReturn($this->objectService);

		$archiveService = $this->createMock(ZGWArchiveDateService::class);
		$logger = $this->createMock(LoggerInterface::class);

		$this->service = new ZGWZaakCloseService($mapperService, $archiveService, $this->registry, $logger);
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
	 * isEindStatus() returns true when the status' volgnummer equals the highest
	 * statustype volgnummer for its zaaktype. The statustype is resolved through
	 * the named-parameter find(_extend:) contract.
	 *
	 * @return void
	 */
	public function testIsEindStatusTrueForHighestVolgnummer(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturn('statustype-uuid');

		$statustype = $this->entity(
			[
				'volgnummer' => 3,
				'_extend' => [
					'zaaktype' => [
						'_extend' => [
							'statustypen' => [
								['volgnummer' => 1],
								['volgnummer' => 2],
								['volgnummer' => 3],
							],
						],
					],
				],
			]
		);

		$this->objectService->expects($this->once())
			->method('find')
			->with(
				'statustype-uuid',
				['_extend.zaaktype' => 'zaaktype', '_extend.statustypen' => 'zaaktype.statustypen']
			)
			->willReturn($statustype);

		$this->assertTrue($this->service->isEindStatus(['statustype' => 'http://example/statustype/3']));
	}//end testIsEindStatusTrueForHighestVolgnummer()

	/**
	 * isEindStatus() returns false for an intermediate volgnummer.
	 *
	 * @return void
	 */
	public function testIsEindStatusFalseForIntermediateVolgnummer(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturn('statustype-uuid');

		$statustype = $this->entity(
			[
				'volgnummer' => 1,
				'_extend' => [
					'zaaktype' => [
						'_extend' => [
							'statustypen' => [
								['volgnummer' => 1],
								['volgnummer' => 2],
							],
						],
					],
				],
			]
		);
		$this->objectService->method('find')->willReturn($statustype);

		$this->assertFalse($this->service->isEindStatus(['statustype' => 'http://example/statustype/1']));
	}//end testIsEindStatusFalseForIntermediateVolgnummer()

	/**
	 * isEindStatus() rejects when the zaaktype has no statustypen — it cannot
	 * determine whether the status is an eindstatus.
	 *
	 * @return void
	 */
	public function testIsEindStatusThrowsWhenNoStatustypen(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturn('statustype-uuid');

		$statustype = $this->entity(
			[
				'volgnummer' => 1,
				'_extend' => ['zaaktype' => ['_extend' => ['statustypen' => []]]],
			]
		);
		$this->objectService->method('find')->willReturn($statustype);

		$this->expectException(CustomValidationException::class);
		$this->service->isEindStatus(['statustype' => 'http://example/statustype/1']);
	}//end testIsEindStatusThrowsWhenNoStatustypen()

	/**
	 * validateClosePrerequisites() is a no-op for a non-eindstatus: the zaak is
	 * never loaded.
	 *
	 * @return void
	 */
	public function testValidateClosePrerequisitesNoopForNonEindstatus(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturn('statustype-uuid');

		$statustype = $this->entity(
			[
				'volgnummer' => 1,
				'_extend' => [
					'zaaktype' => ['_extend' => ['statustypen' => [['volgnummer' => 1], ['volgnummer' => 2]]]],
				],
			]
		);
		// Only the statustype lookup happens — never the zaak.
		$this->objectService->expects($this->once())->method('find')->willReturn($statustype);

		$this->service->validateClosePrerequisites(
			$this->entity(['statustype' => 'http://example/statustype/1', 'zaak' => 'http://example/zaak/1'])
		);
		$this->addToAssertionCount(1);
	}//end testValidateClosePrerequisitesNoopForNonEindstatus()

	/**
	 * validateClosePrerequisites() rejects an eindstatus whose zaak has no
	 * resultaat.
	 *
	 * @return void
	 */
	public function testValidateClosePrerequisitesRejectsMissingResultaat(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturnArgument(0);

		$eindStatustype = $this->entity(
			[
				'volgnummer' => 2,
				'_extend' => ['zaaktype' => ['_extend' => ['statustypen' => [['volgnummer' => 1], ['volgnummer' => 2]]]]],
			]
		);
		$zaak = $this->entity(['identificatie' => 'ZAAK-1']);

		$this->objectService->method('find')->willReturnCallback(
			function ($id) use ($eindStatustype, $zaak) {
				return str_contains((string)$id, 'statustype') ? $eindStatustype : $zaak;
			}
		);

		$this->expectException(CustomValidationException::class);
		$this->service->validateClosePrerequisites(
			$this->entity(
				[
					'statustype' => 'http://example/statustype/2',
					'zaak' => 'http://example/zaak/1',
					'datumStatusGezet' => '2024-01-01',
				]
			)
		);
	}//end testValidateClosePrerequisitesRejectsMissingResultaat()

	/**
	 * validateClosePrerequisites() rejects an eindstatus with an invalid
	 * datumStatusGezet (even when the resultaat + gebruiksrechten are fine).
	 *
	 * @return void
	 */
	public function testValidateClosePrerequisitesRejectsInvalidDate(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturnArgument(0);

		$eindStatustype = $this->entity(
			[
				'volgnummer' => 2,
				'_extend' => ['zaaktype' => ['_extend' => ['statustypen' => [['volgnummer' => 1], ['volgnummer' => 2]]]]],
			]
		);
		$zaak = $this->entity(
			[
				'resultaat' => 'http://example/resultaat/1',
				'zaakinformatieobjecten' => [],
			]
		);

		$this->objectService->method('find')->willReturnCallback(
			function ($id) use ($eindStatustype, $zaak) {
				return str_contains((string)$id, 'statustype') ? $eindStatustype : $zaak;
			}
		);

		$this->expectException(CustomValidationException::class);
		$this->service->validateClosePrerequisites(
			$this->entity(
				[
					'statustype' => 'http://example/statustype/2',
					'zaak' => 'http://example/zaak/1',
					'datumStatusGezet' => 'not-a-date',
				]
			)
		);
	}//end testValidateClosePrerequisitesRejectsInvalidDate()

	/**
	 * validateClosePrerequisites() passes for a complete eindstatus: resultaat
	 * present, no informatieobjecten to check, valid ISO date.
	 *
	 * @return void
	 */
	public function testValidateClosePrerequisitesPassesForCompleteEindstatus(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturnArgument(0);

		$eindStatustype = $this->entity(
			[
				'volgnummer' => 2,
				'_extend' => ['zaaktype' => ['_extend' => ['statustypen' => [['volgnummer' => 1], ['volgnummer' => 2]]]]],
			]
		);
		$zaak = $this->entity(
			[
				'resultaat' => 'http://example/resultaat/1',
				'zaakinformatieobjecten' => [],
			]
		);

		$this->objectService->method('find')->willReturnCallback(
			function ($id) use ($eindStatustype, $zaak) {
				return str_contains((string)$id, 'statustype') ? $eindStatustype : $zaak;
			}
		);

		$this->service->validateClosePrerequisites(
			$this->entity(
				[
					'statustype' => 'http://example/statustype/2',
					'zaak' => 'http://example/zaak/1',
					'datumStatusGezet' => '2024-06-10',
				]
			)
		);
		$this->addToAssertionCount(1);
	}//end testValidateClosePrerequisitesPassesForCompleteEindstatus()
}//end class
