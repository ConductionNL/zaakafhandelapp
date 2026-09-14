<?php

/**
 * CallService connection-report tests.
 *
 * `CallService` is the one place a ZRC or BRC call leaves the app, so it is the
 * one place that knows whether the connection answered. These tests guard the
 * wiring: a real transfer reaches the reporter with what it met, the call
 * fails exactly as it did before, and a CallService built without a reporter
 * behaves as it always did.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Service
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://conduction.nl
 *
 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Service;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use OCA\ZaakAfhandelApp\Service\CallService;
use OCA\ZaakAfhandelApp\Service\ConnectionReportService;
use OCP\IAppConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit tests for the report CallService sends after each transfer.
 *
 * @covers \OCA\ZaakAfhandelApp\Service\CallService
 */
class CallServiceConnectionReportTest extends TestCase {

	/**
	 * An address where nothing listens: port 1 on the loopback interface.
	 *
	 * The kernel refuses the connection at once, so the transfer ends without
	 * an answer and without leaving the machine.
	 *
	 * @var string
	 */
	private const CLOSED_PORT = 'http://127.0.0.1:1/zaken/api/v1/';

	/**
	 * App config holding one ZRC location and no auth.
	 *
	 * @return IAppConfig&MockObject
	 */
	private function appConfig(): IAppConfig&MockObject {
		$appConfig = $this->createMock(originalClassName: IAppConfig::class);
		$appConfig->method('getValueString')->willReturnCallback(
			static fn (string $app, string $key, string $default = ''): string => match ($key) {
				'zrcLocation' => self::CLOSED_PORT,
				default => $default,
			}
		);

		return $appConfig;
	}//end appConfig()

	/**
	 * A ZRC that does not answer reports no answer, and the call still fails.
	 *
	 * This is a real Guzzle transfer, not a stubbed client: it proves Guzzle
	 * calls `on_stats` for a transfer that got no response at all.
	 *
	 * @return void
	 */
	public function testAZrcThatDoesNotAnswerReportsNoAnswerAndTheCallStillFails(): void {
		$reports = $this->createMock(originalClassName: ConnectionReportService::class);
		$reports->expects($this->once())->method('reportCall')->with('zrc', null)->willReturn(true);

		$service = new CallService(config: $this->appConfig(), connectionReports: $reports);

		$this->expectException(ConnectException::class);
		$service->index(source: 'zrc', endpoint: 'statussen');
	}//end testAZrcThatDoesNotAnswerReportsNoAnswerAndTheCallStillFails()

	/**
	 * Without a reporter the call fails the same way and nothing is reported.
	 *
	 * @return void
	 */
	public function testWithoutAReporterTheCallFailsTheSameWay(): void {
		$service = new CallService(config: $this->appConfig());

		$this->expectException(ConnectException::class);
		$service->index(source: 'zrc', endpoint: 'statussen');
	}//end testWithoutAReporterTheCallFailsTheSameWay()

	/**
	 * A transfer with an answer hands its HTTP status and its source to the reporter.
	 *
	 * @return void
	 */
	public function testAnAnswerHandsItsStatusAndSourceToTheReporter(): void {
		$reports = $this->createMock(originalClassName: ConnectionReportService::class);
		$reports->expects($this->once())->method('reportCall')->with('brc', 503)->willReturn(true);

		$service  = new CallService(config: $this->appConfig(), connectionReports: $reports);
		$observer = (new ReflectionMethod(CallService::class, 'statsObserver'))->invoke($service, 'brc');

		$observer(new TransferStats(new Request('GET', 'https://brc.gemeente.example/besluiten'), new Response(503)));
	}//end testAnAnswerHandsItsStatusAndSourceToTheReporter()
}//end class
