<?php

/**
 * Unit tests for ZaakTermijnService.
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
use OCA\OpenRegister\Service\ObjectService;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCA\ZaakAfhandelApp\Service\ZaakTermijnService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Locks the REQ-001 derivation matrix: doorlooptijd → uiterlijkeEinddatumAfdoening
 * and servicenorm → einddatumGepland, explicit-value respect, startdatum →
 * registratiedatum fallback, and skip-on-missing-zaaktype/term.
 */
final class ZaakTermijnServiceTest extends TestCase {
	/** @var ObjectService&\PHPUnit\Framework\MockObject\MockObject */
	private $objectService;

	/** @var ZGWRegistryService&\PHPUnit\Framework\MockObject\MockObject */
	private $registry;

	private ZaakTermijnService $service;

	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);
		$this->registry = $this->createMock(ZGWRegistryService::class);

		$mapperService = $this->createMock(ObjectMapperService::class);
		$mapperService->method('getOpenRegisters')->willReturn($this->objectService);

		$this->service = new ZaakTermijnService($mapperService, $this->registry, $this->createMock(LoggerInterface::class));
	}//end setUp()

	private function entity(array $data): ObjectEntity {
		$e = new ObjectEntity();
		$e->setObject($data);
		return $e;
	}//end entity()

	private function stubCaseType(array $caseType): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturn('zt-uuid');
		$this->objectService->method('find')->willReturn($this->entity($caseType));
	}//end stubZaaktype()

	public function testDerivesUiterlijkeEinddatumFromDoorlooptijd(): void {
		$this->stubCaseType(['doorlooptijd' => 'P56D']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'startdatum' => '2026-06-01',
		]);

		$this->service->deriveTermijnen($case);

		$this->assertSame('2026-07-27', $case->jsonSerialize()['uiterlijkeEinddatumAfdoening']);
	}//end testDerivesUiterlijkeEinddatumFromDoorlooptijd()

	public function testDerivesEinddatumGeplandFromServicenorm(): void {
		$this->stubCaseType(['servicenorm' => '14']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'startdatum' => '2026-06-01',
		]);

		$this->service->deriveTermijnen($case);

		$this->assertSame('2026-06-15', $case->jsonSerialize()['einddatumGepland']);
	}//end testDerivesEinddatumGeplandFromServicenorm()

	public function testExplicitValuesAreNeverOverridden(): void {
		// The service must not even resolve the zaaktype when both are supplied.
		$this->objectService->expects($this->never())->method('find');

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'startdatum' => '2026-06-01',
			'uiterlijkeEinddatumAfdoening' => '2026-09-09',
			'einddatumGepland' => '2026-08-08',
		]);

		$this->service->deriveTermijnen($case);

		$out = $case->jsonSerialize();
		$this->assertSame('2026-09-09', $out['uiterlijkeEinddatumAfdoening']);
		$this->assertSame('2026-08-08', $out['einddatumGepland']);
	}//end testExplicitValuesAreNeverOverridden()

	public function testFallsBackToRegistratiedatumWhenNoStartdatum(): void {
		$this->stubCaseType(['doorlooptijd' => 'P7D']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'registratiedatum' => '2026-06-10',
		]);

		$this->service->deriveTermijnen($case);

		$this->assertSame('2026-06-17', $case->jsonSerialize()['uiterlijkeEinddatumAfdoening']);
	}//end testFallsBackToRegistratiedatumWhenNoStartdatum()

	public function testZaaktypeWithoutTermsLeavesFieldsEmpty(): void {
		$this->stubCaseType(['doorlooptijd' => '', 'servicenorm' => '']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'startdatum' => '2026-06-01',
		]);

		$this->service->deriveTermijnen($case);

		$out = $case->jsonSerialize();
		$this->assertArrayNotHasKey('uiterlijkeEinddatumAfdoening', $out);
		$this->assertArrayNotHasKey('einddatumGepland', $out);
	}//end testZaaktypeWithoutTermsLeavesFieldsEmpty()

	public function testNoZaaktypeIsNoop(): void {
		$this->objectService->expects($this->never())->method('find');

		$case = $this->entity(['startdatum' => '2026-06-01']);
		$this->service->deriveTermijnen($case);

		$this->assertArrayNotHasKey('uiterlijkeEinddatumAfdoening', $case->jsonSerialize());
	}//end testNoZaaktypeIsNoop()

	public function testUnparsableDurationIsSkipped(): void {
		$this->stubCaseType(['doorlooptijd' => 'not-a-duration']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'startdatum' => '2026-06-01',
		]);

		$this->service->deriveTermijnen($case);

		$this->assertArrayNotHasKey('uiterlijkeEinddatumAfdoening', $case->jsonSerialize());
	}//end testUnparsableDurationIsSkipped()
}//end class
