<?php

/**
 * Unit tests for ZGWLogicService.
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
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\OpenRegister\Service\ObjectService;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCA\ZaakAfhandelApp\Service\ZGWLogicService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use PHPUnit\Framework\TestCase;

/**
 * Tests ZGWLogicService — besluit + OIO operations.
 *
 * Locks: createZaakBesluit() tolerates a null zaaktype.besluittypen (#282 bug-2)
 * and rejects a besluittype not on the zaaktype; createZaakBesluit() resolves
 * the zaak via the named-parameter find(_extend: ['zaaktype']) contract;
 * deleteZaakTypeInformatieObjecttype() returns early when no IOT is found
 * (#282 bug-3).
 */
class ZGWLogicServiceTest extends TestCase {

	/**
	 * @var ObjectService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $objectService;

	/**
	 * @var RegisterMapper&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $registerMapper;

	/**
	 * @var SchemaMapper&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $schemaMapper;

	/**
	 * @var ZGWRegistryService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $registry;

	/**
	 * @var ZGWLogicService
	 */
	private $service;

	/**
	 * Wire the service with mocked collaborators.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);
		$this->registerMapper = $this->createMock(RegisterMapper::class);
		$this->schemaMapper = $this->createMock(SchemaMapper::class);
		$this->registry = $this->createMock(ZGWRegistryService::class);

		$mapperService = $this->createMock(ObjectMapperService::class);
		$mapperService->method('getOpenRegisters')->willReturn($this->objectService);

		$this->service = new ZGWLogicService(
			$mapperService,
			$this->registerMapper,
			$this->schemaMapper,
			$this->registry
		);
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
	 * createZaakBesluit() with no zaak link is a no-op (returns immediately).
	 *
	 * @return void
	 */
	public function testCreateZaakBesluitWithoutZaakIsNoop(): void {
		$this->objectService->expects($this->never())->method('find');
		$this->objectService->expects($this->never())->method('saveObject');

		$this->service->createZaakBesluit($this->entity(['url' => 'http://example/besluit/1']));
		$this->addToAssertionCount(1);
	}//end testCreateZaakBesluitWithoutZaakIsNoop()

	/**
	 * REGRESSION (#282 bug-2): a zaaktype with a null besluittypen must not
	 * crash createZaakBesluit() — it is treated as "not allowed" and the
	 * besluittype is rejected via CustomValidationException, not a TypeError.
	 *
	 * @return void
	 */
	public function testCreateZaakBesluitNullBesluittypenRejectsGracefully(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturnArgument(0);

		$zaak = $this->entity(['zaaktype' => ['besluittypen' => null]]);
		$besluittype = $this->entity(['omschrijving' => 'primair besluit']);

		$this->objectService->method('find')->willReturnCallback(
			function ($id, $_extend = []) use ($zaak, $besluittype) {
				return in_array('zaaktype', (array)$_extend, true) ? $zaak : $besluittype;
			}
		);

		$this->expectException(CustomValidationException::class);
		$this->service->createZaakBesluit(
			$this->entity(
				[
					'zaak' => 'http://example/zaak/1',
					'besluittype' => 'http://example/besluittype/1',
					'url' => 'http://example/besluit/1',
				]
			)
		);
	}//end testCreateZaakBesluitNullBesluittypenRejectsGracefully()

	/**
	 * createZaakBesluit() resolves the zaak through find(_extend: ['zaaktype'])
	 * and, when the besluittype is on the zaaktype, persists a new zaakbesluit.
	 *
	 * @return void
	 */
	public function testCreateZaakBesluitAllowedTypePersistsZaakBesluit(): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturnArgument(0);
		$this->registry->method('getZrcRegister')->willReturn('zrc');
		$this->registry->method('getZaakBesluitSchema')->willReturn('zaakbesluit');

		$zaak = $this->entity(['zaaktype' => ['besluittypen' => ['primair besluit']]]);
		$besluittype = $this->entity(['omschrijving' => 'primair besluit']);

		// Capture _extend for the zaak lookup to lock the named-parameter usage.
		$zaakExtend = null;
		$this->objectService->method('find')->willReturnCallback(
			function ($id, $_extend = []) use ($zaak, $besluittype, &$zaakExtend) {
				if (in_array('zaaktype', (array)$_extend, true) === true) {
					$zaakExtend = $_extend;
					return $zaak;
				}

				return $besluittype;
			}
		);

		$saved = null;
		$this->objectService->method('saveObject')->willReturnCallback(
			function ($object) use (&$saved) {
				$saved = $object;
				return $object;
			}
		);

		$this->service->createZaakBesluit(
			$this->entity(
				[
					'zaak' => 'http://example/zaak/1',
					'besluittype' => 'http://example/besluittype/1',
					'url' => 'http://example/besluit/1',
				]
			)
		);

		$this->assertSame(['zaaktype'], $zaakExtend);
		$this->assertInstanceOf(ObjectEntity::class, $saved);
		$this->assertSame('http://example/zaak/1', $saved->jsonSerialize()['zaak']);
		$this->assertSame('http://example/besluit/1', $saved->jsonSerialize()['besluit']);
	}//end testCreateZaakBesluitAllowedTypePersistsZaakBesluit()

	/**
	 * REGRESSION (#282 bug-3): deleteZaakTypeInformatieObjecttype() returns early
	 * when no matching informatieobjecttype is found (array_shift -> null),
	 * never attempting to serialize null or save.
	 *
	 * @return void
	 */
	public function testDeleteZaakTypeInformatieObjecttypeNoIotReturnsEarly(): void {
		$this->registry->method('getZtcRegister')->willReturn('ztc');
		$this->registry->method('getIOTSchema')->willReturn('iot');

		$this->registerMapper->method('find')->willReturn($this->idDouble(1));
		$this->schemaMapper->method('find')->willReturn($this->idDouble(2));

		// No informatieobjecttype matches.
		$this->objectService->method('findAll')->willReturn([]);
		$this->objectService->expects($this->never())->method('saveObject');

		$this->service->deleteZaakTypeInformatieObjecttype(
			$this->entity(
				[
					'informatieobjecttype' => 'factuur',
					'zaaktype' => 'http://example/zaaktype/1',
				]
			)
		);
		$this->addToAssertionCount(1);
	}//end testDeleteZaakTypeInformatieObjecttypeNoIotReturnsEarly()

	/**
	 * Build a double exposing getId() for register/schema mapper lookups.
	 *
	 * @param integer $id The id value.
	 *
	 * @return object
	 */
	private function idDouble(int $id): object {
		return new class($id) {
			/**
			 * @param integer $id Id.
			 */
			public function __construct(
				private int $id,
			) {
			}//end __construct()

			/**
			 * @return integer
			 */
			public function getId(): int {
				return $this->id;
			}//end getId()
		};
	}//end idDouble()
}//end class
