<?php

/**
 * Unit tests for ZGWZaakValidationService.
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
use OCA\ZaakAfhandelApp\Service\ZGWZaakValidationService;
use PHPUnit\Framework\TestCase;

/**
 * Tests ZGWZaakValidationService archive-prerequisite + null-guard fixes.
 *
 * Locks: an empty/absent archiefstatus means "not started" and must NOT reject a
 * fresh create; the gegevensgroepen + productenOfDiensten checks must no-op when
 * their fields are absent.
 */
class ZGWZaakValidationServiceTest extends TestCase {

	/**
	 * @var ObjectService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $objectService;

	/**
	 * @var ZGWZaakValidationService
	 */
	private $service;

	/**
	 * Build the service with a mocked OpenRegister ObjectService.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);

		$mapperService = $this->createMock(ObjectMapperService::class);
		$mapperService->method('getOpenRegisters')->willReturn($this->objectService);

		$this->service = new ZGWZaakValidationService($mapperService);
	}//end setUp()

	/**
	 * Build a real ObjectEntity carrying the given payload.
	 *
	 * @param array<string,mixed> $data The object payload.
	 *
	 * @return ObjectEntity
	 */
	private function case(array $data): ObjectEntity {
		$entity = new ObjectEntity();
		$entity->setObject($data);
		return $entity;
	}//end zaak()

	/**
	 * REGRESSION (#fix): an absent archiefstatus is "not started" — a fresh
	 * create must NOT be rejected. checkArchivePrerequisites returns without
	 * throwing and without inspecting archiefnominatie/archiefactiedatum.
	 *
	 * @return void
	 */
	public function testArchivePrerequisitesAbsentArchiefstatusDoesNotReject(): void {
		$case = $this->case(['identificatie' => 'ZAAK-1']);
		$this->objectService->method('renderEntity')->willReturn($case->jsonSerialize());

		// No exception expected.
		$this->service->checkArchivePrerequisites($case);
		$this->addToAssertionCount(1);
	}//end testArchivePrerequisitesAbsentArchiefstatusDoesNotReject()

	/**
	 * REGRESSION: an explicitly empty-string archiefstatus is also "not
	 * started" and must not reject.
	 *
	 * @return void
	 */
	public function testArchivePrerequisitesEmptyArchiefstatusDoesNotReject(): void {
		$case = $this->case(['archiefstatus' => '']);
		$this->objectService->method('renderEntity')->willReturn($case->jsonSerialize());

		$this->service->checkArchivePrerequisites($case);
		$this->addToAssertionCount(1);
	}//end testArchivePrerequisitesEmptyArchiefstatusDoesNotReject()

	/**
	 * The explicit 'nog_te_archiveren' sentinel is also treated as not started.
	 *
	 * @return void
	 */
	public function testArchivePrerequisitesNogTeArchiverenDoesNotReject(): void {
		$case = $this->case(['archiefstatus' => 'nog_te_archiveren']);
		$this->objectService->method('renderEntity')->willReturn($case->jsonSerialize());

		$this->service->checkArchivePrerequisites($case);
		$this->addToAssertionCount(1);
	}//end testArchivePrerequisitesNogTeArchiverenDoesNotReject()

	/**
	 * When archiving HAS started (archiefstatus set) but archiefnominatie is
	 * missing, the check rejects — proving the guard does not over-skip.
	 *
	 * @return void
	 */
	public function testArchivePrerequisitesStartedWithoutNominatieRejects(): void {
		$case = $this->case(
			[
				'archiefstatus' => 'gearchiveerd',
				'zaakinformatieobjecten' => [],
			]
		);
		$this->objectService->method('renderEntity')->willReturn($case->jsonSerialize());

		$this->expectException(CustomValidationException::class);
		$this->service->checkArchivePrerequisites($case);
	}//end testArchivePrerequisitesStartedWithoutNominatieRejects()

	/**
	 * When archiving has started and both archiefnominatie + archiefactiedatum
	 * are set (and no informatieobjecten to validate), the check passes.
	 *
	 * @return void
	 */
	public function testArchivePrerequisitesStartedWithCompleteMetadataPasses(): void {
		$case = $this->case(
			[
				'archiefstatus' => 'gearchiveerd',
				'archiefnominatie' => 'vernietigen',
				'archiefactiedatum' => '2030-01-01',
				'zaakinformatieobjecten' => [],
			]
		);
		$this->objectService->method('renderEntity')->willReturn($case->jsonSerialize());

		$this->service->checkArchivePrerequisites($case);
		$this->addToAssertionCount(1);
	}//end testArchivePrerequisitesStartedWithCompleteMetadataPasses()

	/**
	 * checkGegevensgroepen null-guard: no verlenging/opschorting present means
	 * nothing to validate — it must not throw.
	 *
	 * @return void
	 */
	public function testGegevensgroepenWithoutGroupsPasses(): void {
		$this->service->checkGegevensgroepen($this->case(['identificatie' => 'ZAAK-2']));
		$this->addToAssertionCount(1);
	}//end testGegevensgroepenWithoutGroupsPasses()

	/**
	 * checkGegevensgroepen rejects when a present verlenging is missing a
	 * required field (reden/duur).
	 *
	 * @return void
	 */
	public function testGegevensgroepenIncompleteVerlengingRejects(): void {
		$case = $this->case(['verlenging' => ['reden' => 'x']]);

		$this->expectException(CustomValidationException::class);
		$this->service->checkGegevensgroepen($case);
	}//end testGegevensgroepenIncompleteVerlengingRejects()

	/**
	 * checkGegevensgroepen passes when a present verlenging has all required
	 * fields.
	 *
	 * @return void
	 */
	public function testGegevensgroepenCompleteVerlengingPasses(): void {
		$case = $this->case(['verlenging' => ['reden' => 'x', 'duur' => 'P1W']]);

		$this->service->checkGegevensgroepen($case);
		$this->addToAssertionCount(1);
	}//end testGegevensgroepenCompleteVerlengingPasses()

	/**
	 * checkProductenOfDiensten null-guard: absent productenOfDiensten means the
	 * check returns immediately and never resolves a zaaktype.
	 *
	 * @return void
	 */
	public function testProductenOfDienstenAbsentSkipsZaaktypeLookup(): void {
		$this->objectService->expects($this->never())->method('find');

		$this->service->checkProductenOfDiensten($this->case(['identificatie' => 'ZAAK-3']));
		$this->addToAssertionCount(1);
	}//end testProductenOfDienstenAbsentSkipsZaaktypeLookup()

	/**
	 * checkProductenOfDiensten with a product not on the zaaktype rejects.
	 *
	 * @return void
	 */
	public function testProductenOfDienstenNotOnZaaktypeRejects(): void {
		$case = $this->case(
			[
				'productenOfDiensten' => ['http://example/product/forbidden'],
				'zaaktype' => 'http://example/zaaktype/42',
			]
		);

		$caseType = $this->case(['productenOfDiensten' => ['http://example/product/allowed']]);
		$this->objectService->method('find')->willReturn($caseType);

		$this->expectException(CustomValidationException::class);
		$this->service->checkProductenOfDiensten($case);
	}//end testProductenOfDienstenNotOnZaaktypeRejects()
}//end class
