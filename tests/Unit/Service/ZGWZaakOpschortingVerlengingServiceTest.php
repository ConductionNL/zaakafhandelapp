<?php

/**
 * Unit tests for ZGWZaakOpschortingVerlengingService.
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

use DateTimeImmutable;
use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCA\OpenRegister\Service\ObjectService;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakOpschortingVerlengingService;
use PHPUnit\Framework\TestCase;

/**
 * Locks the opschorting/verlenging contract: deadline-shift math on resume and
 * extend, the policy gates (zaaktype switches + verlengingstermijn cap), and
 * the refusal matrix (closed, suspended, double-verlenging, empty reden,
 * forbidding zaaktype, duur over the termijn).
 */
final class ZGWZaakOpschortingVerlengingServiceTest extends TestCase {
	/** @var ObjectService&\PHPUnit\Framework\MockObject\MockObject */
	private $objectService;

	/** @var ZGWRegistryService&\PHPUnit\Framework\MockObject\MockObject */
	private $registry;

	private ZGWZaakOpschortingVerlengingService $service;

	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);
		$this->registry = $this->createMock(ZGWRegistryService::class);

		$mapperService = $this->createMock(ObjectMapperService::class);
		$mapperService->method('getOpenRegisters')->willReturn($this->objectService);

		$this->service = new ZGWZaakOpschortingVerlengingService($mapperService, $this->registry);
	}//end setUp()

	private function entity(array $data): ObjectEntity {
		$e = new ObjectEntity();
		$e->setObject($data);
		return $e;
	}//end entity()

	/**
	 * Stub the zaaktype resolution so the policy gates pass/fail as configured.
	 *
	 * @param array<string,mixed> $caseType The zaaktype payload to return.
	 */
	private function stubCaseType(array $caseType): void {
		$this->registry->method('getObjectIdByEndpointUrl')->willReturn('zt-uuid');
		$this->objectService->method('find')->willReturn($this->entity($caseType));
	}//end stubZaaktype()

	public function testSuspendRecordsStartAndKeepsDeadlines(): void {
		$this->stubCaseType(['opschortingEnAanhoudingMogelijk' => 'true']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'einddatumGepland' => '2026-07-01',
			'uiterlijkeEinddatumAfdoening' => '2026-07-01',
			'opschorting' => ['indicatie' => true, 'reden' => 'Wacht op stukken'],
		]);

		$now = new DateTimeImmutable('2026-06-01T00:00:00+00:00');
		$this->service->applyTransitions($case, [], $now);

		$out = $case->jsonSerialize();
		$this->assertTrue($out['opschorting']['indicatie']);
		$this->assertArrayHasKey('_opschortingGestart', $out['opschorting']);
		// Deadlines are unchanged on suspend.
		$this->assertSame('2026-07-01', $out['einddatumGepland']);
		$this->assertSame('2026-07-01', $out['uiterlijkeEinddatumAfdoening']);
	}//end testSuspendRecordsStartAndKeepsDeadlines()

	public function testResumeShiftsDeadlinesByElapsedSuspension(): void {
		$old = [
			'einddatumGepland' => '2026-07-01',
			'uiterlijkeEinddatumAfdoening' => '2026-07-01',
			'opschorting' => ['indicatie' => true, 'reden' => 'x', '_opschortingGestart' => '2026-06-01T00:00:00+00:00'],
		];
		$case = $this->entity([
			'einddatumGepland' => '2026-07-01',
			'uiterlijkeEinddatumAfdoening' => '2026-07-01',
			'opschorting' => ['indicatie' => false, 'reden' => 'x'],
		]);

		// Resumed 10 days after suspension started.
		$now = new DateTimeImmutable('2026-06-11T00:00:00+00:00');
		$this->service->applyTransitions($case, $old, $now);

		$out = $case->jsonSerialize();
		$this->assertFalse($out['opschorting']['indicatie']);
		$this->assertSame('2026-07-11', $out['uiterlijkeEinddatumAfdoening']);
		$this->assertSame('2026-07-11', $out['einddatumGepland']);
		$this->assertArrayNotHasKey('_opschortingGestart', $out['opschorting']);
	}//end testResumeShiftsDeadlinesByElapsedSuspension()

	public function testSuspendForbiddenByZaaktypeRefused(): void {
		$this->stubCaseType(['opschortingEnAanhoudingMogelijk' => 'false']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'opschorting' => ['indicatie' => true, 'reden' => 'reden'],
		]);

		$this->expectException(CustomValidationException::class);
		$this->service->applyTransitions($case, [], new DateTimeImmutable());
	}//end testSuspendForbiddenByZaaktypeRefused()

	public function testSuspendWithoutRedenRefused(): void {
		$this->stubCaseType(['opschortingEnAanhoudingMogelijk' => 'true']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'opschorting' => ['indicatie' => true, 'reden' => '  '],
		]);

		$this->expectException(CustomValidationException::class);
		$this->service->applyTransitions($case, [], new DateTimeImmutable());
	}//end testSuspendWithoutRedenRefused()

	public function testSuspendClosedZaakRefused(): void {
		$old = ['einddatum' => '2026-05-01'];
		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'opschorting' => ['indicatie' => true, 'reden' => 'reden'],
		]);

		$this->expectException(CustomValidationException::class);
		$this->service->applyTransitions($case, $old, new DateTimeImmutable());
	}//end testSuspendClosedZaakRefused()

	public function testExtendShiftsDeadlines(): void {
		$this->stubCaseType(['verlengingMogelijk' => 'true']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'einddatumGepland' => '2026-07-01',
			'uiterlijkeEinddatumAfdoening' => '2026-07-01',
			'verlenging' => ['reden' => 'meer tijd', 'duur' => 'P14D'],
		]);

		$this->service->applyTransitions($case, [], new DateTimeImmutable());

		$out = $case->jsonSerialize();
		$this->assertSame('2026-07-15', $out['uiterlijkeEinddatumAfdoening']);
		$this->assertSame('2026-07-15', $out['einddatumGepland']);
		$this->assertSame('P14D', $out['verlenging']['duur']);
	}//end testExtendShiftsDeadlines()

	public function testExtendExceedingVerlengingstermijnRefused(): void {
		$this->stubCaseType(['verlengingMogelijk' => 'true', 'verlengingstermijn' => 'P14D']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'verlenging' => ['reden' => 'meer tijd', 'duur' => 'P30D'],
		]);

		$this->expectException(CustomValidationException::class);
		$this->service->applyTransitions($case, [], new DateTimeImmutable());
	}//end testExtendExceedingVerlengingstermijnRefused()

	public function testSecondVerlengingRefused(): void {
		$old = ['verlenging' => ['reden' => 'eerste', 'duur' => 'P7D']];
		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'verlenging' => ['reden' => 'tweede', 'duur' => 'P7D'],
		]);

		$this->expectException(CustomValidationException::class);
		$this->service->applyTransitions($case, $old, new DateTimeImmutable());
	}//end testSecondVerlengingRefused()

	public function testExtendForbiddenByZaaktypeRefused(): void {
		$this->stubCaseType(['verlengingMogelijk' => 'false']);

		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'verlenging' => ['reden' => 'meer tijd', 'duur' => 'P7D'],
		]);

		$this->expectException(CustomValidationException::class);
		$this->service->applyTransitions($case, [], new DateTimeImmutable());
	}//end testExtendForbiddenByZaaktypeRefused()

	public function testExtendSuspendedZaakRefused(): void {
		// The new state still carries opschorting.indicatie = true (unchanged from old),
		// so the verlenging must be refused.
		$old = ['opschorting' => ['indicatie' => true, 'reden' => 'x']];
		$case = $this->entity([
			'zaaktype' => 'http://example/zaaktype/1',
			'opschorting' => ['indicatie' => true, 'reden' => 'x'],
			'verlenging' => ['reden' => 'meer tijd', 'duur' => 'P7D'],
		]);

		$this->expectException(CustomValidationException::class);
		$this->service->applyTransitions($case, $old, new DateTimeImmutable());
	}//end testExtendSuspendedZaakRefused()

	public function testNoTransitionLeavesZaakUntouched(): void {
		$this->objectService->expects($this->never())->method('saveObject');

		$case = $this->entity([
			'einddatumGepland' => '2026-07-01',
			'opschorting' => ['indicatie' => false, 'reden' => ''],
		]);

		$this->service->applyTransitions($case, ['opschorting' => ['indicatie' => false]], new DateTimeImmutable());

		$this->assertSame('2026-07-01', $case->jsonSerialize()['einddatumGepland']);
	}//end testNoTransitionLeavesZaakUntouched()
}//end class
