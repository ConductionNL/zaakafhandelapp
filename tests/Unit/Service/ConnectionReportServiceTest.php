<?php

/**
 * ConnectionReportService unit tests.
 *
 * The service tells integriq's connection registry what the last ZRC or BRC
 * call met, and asks integriq to resolve a connection again after a save.
 * Every test guards one way it could quietly stop telling the truth: calling
 * a refused key configured, reporting on every request, never reporting a
 * change, leaking request data into the row, turning a listener's failure into
 * a failed call, or touching app config when integriq is not installed.
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

use OCA\Integriq\Event\ConnectionRefreshRequestedEvent;
use OCA\Integriq\Event\ConnectionStatusReportedEvent;
use OCA\ZaakAfhandelApp\Service\ConnectionReportService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IAppConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use RuntimeException;

/**
 * Unit tests for ConnectionReportService.
 *
 * @covers \OCA\ZaakAfhandelApp\Service\ConnectionReportService
 */
class ConnectionReportServiceTest extends TestCase {

	/**
	 * The ZRC address the fixture app config holds.
	 *
	 * @var string
	 */
	private const ZRC_LOCATION = 'https://zrc.gemeente.example/zaken/api/v1/';

	/**
	 * Mocked event dispatcher.
	 *
	 * @var IEventDispatcher&MockObject
	 */
	private IEventDispatcher&MockObject $dispatcher;

	/**
	 * Mocked app config, backed by $this->store.
	 *
	 * @var IAppConfig&MockObject
	 */
	private IAppConfig&MockObject $appConfig;

	/**
	 * Mocked logger.
	 *
	 * @var LoggerInterface&MockObject
	 */
	private LoggerInterface&MockObject $logger;

	/**
	 * The app-config values, keyed by config key.
	 *
	 * @var array<string, string>
	 */
	private array $store = [];

	/**
	 * The Unix time the clock answers.
	 *
	 * @var int
	 */
	private int $now = 1_760_000_000;

	/**
	 * Every event handed to the dispatcher.
	 *
	 * @var array<int, Event>
	 */
	private array $sent = [];

	/**
	 * Set up the fixtures.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->sent  = [];
		$this->store = ['zrcLocation' => self::ZRC_LOCATION, 'brcLocation' => 'https://brc.gemeente.example/api/v1'];

		$this->dispatcher = $this->createMock(originalClassName: IEventDispatcher::class);
		$this->dispatcher->method('dispatchTyped')->willReturnCallback(
			function (Event $event): void {
				$this->sent[] = $event;
			}
		);

		$this->appConfig = $this->createMock(originalClassName: IAppConfig::class);
		$this->appConfig->method('getValueString')->willReturnCallback(
			fn (string $app, string $key, string $default = ''): string => ($this->store[$key] ?? $default)
		);
		$this->appConfig->method('setValueString')->willReturnCallback(
			function (string $app, string $key, string $value): bool {
				$this->store[$key] = $value;
				return true;
			}
		);
		$this->appConfig->method('deleteKey')->willReturnCallback(
			function (string $app, string $key): void {
				unset($this->store[$key]);
			}
		);

		$this->logger = $this->createMock(originalClassName: LoggerInterface::class);
	}//end setUp()

	/**
	 * The service as production builds it.
	 *
	 * @return ConnectionReportService
	 */
	private function service(): ConnectionReportService {
		$time = $this->createMock(originalClassName: ITimeFactory::class);
		$time->method('getTime')->willReturnCallback(fn (): int => $this->now);

		return new ConnectionReportService(
			eventDispatcher: $this->dispatcher,
			appConfig: $this->appConfig,
			timeFactory: $time,
			logger: $this->logger,
		);
	}//end service()

	/**
	 * The service as it behaves on an instance without integriq.
	 *
	 * Only the class lookup is replaced. The stubs make both event classes
	 * resolvable in this process, so absence is simulated at the one seam
	 * that asks.
	 *
	 * @return ConnectionReportService
	 */
	private function serviceWithoutIntegriq(): ConnectionReportService {
		return new class($this->dispatcher, $this->appConfig, $this->createMock(originalClassName: ITimeFactory::class), $this->logger) extends ConnectionReportService {

			/**
			 * Integriq is not installed, so no class resolves.
			 *
			 * @param string $eventClass The class name asked for.
			 *
			 * @return string|null Always null.
			 */
			protected function resolveEventClass(string $eventClass): ?string {
				return null;
			}//end resolveEventClass()
		};
	}//end serviceWithoutIntegriq()

	/**
	 * An answer reports configured, with this app's id, the key and the host.
	 *
	 * @return void
	 */
	public function testAnAnswerReportsConfigured(): void {
		$this->assertTrue(condition: $this->service()->reportCall(source: 'zrc', httpStatus: 200));

		$this->assertCount(expectedCount: 1, haystack: $this->sent);
		$event = $this->sent[0];
		$this->assertInstanceOf(expected: ConnectionStatusReportedEvent::class, actual: $event);
		$this->assertSame(expected: 'zaakafhandelapp', actual: $event->app);
		$this->assertSame(expected: 'zrc', actual: $event->key);
		$this->assertSame(expected: 'configured', actual: $event->status);
		$this->assertSame(expected: 'The ZRC at zrc.gemeente.example answered the last call.', actual: $event->message);
		$this->assertSame(expected: 'configured|' . $this->now, actual: $this->store['connection_report_zrc']);
	}//end testAnAnswerReportsConfigured()

	/**
	 * Each outcome maps to the status the design names.
	 *
	 * A 404, 400 or 500 is about one request, not about the connection, so it
	 * reads configured. No answer, a refused key and a gateway error read error.
	 *
	 * @return void
	 */
	public function testEachOutcomeMapsToTheDesignedStatus(): void {
		$service  = $this->service();
		$expected = [
			200 => 'configured',
			400 => 'configured',
			404 => 'configured',
			500 => 'configured',
			401 => 'error',
			403 => 'error',
			502 => 'error',
			503 => 'error',
			504 => 'error',
		];

		foreach ($expected as $httpStatus => $status) {
			$this->assertSame(expected: $status, actual: $service->observe(source: 'brc', httpStatus: $httpStatus)[0], message: 'HTTP ' . $httpStatus);
		}

		$this->assertSame(
			expected: ['error', 'The last call to the BRC at brc.gemeente.example got no answer.'],
			actual: $service->observe(source: 'brc', httpStatus: null)
		);
		$this->assertSame(
			expected: ['error', 'The ZRC at zrc.gemeente.example refused the key (HTTP 401).'],
			actual: $service->observe(source: 'zrc', httpStatus: 401)
		);
		$this->assertSame(
			expected: ['error', 'The ZRC at zrc.gemeente.example answered HTTP 503 on the last call.'],
			actual: $service->observe(source: 'zrc', httpStatus: 503)
		);
	}//end testEachOutcomeMapsToTheDesignedStatus()

	/**
	 * An empty location reports unconfigured and names the key.
	 *
	 * @return void
	 */
	public function testAnEmptyLocationReportsUnconfigured(): void {
		$this->store['zrcLocation'] = '  ';

		$this->assertSame(
			expected: ['unconfigured', 'No ZRC address is set. Set zrcLocation to reach it.'],
			actual: $this->service()->observe(source: 'zrc', httpStatus: null)
		);
	}//end testAnEmptyLocationReportsUnconfigured()

	/**
	 * The message carries the host and nothing else from the address.
	 *
	 * A location with credentials or a path must not put them on a row every
	 * admin reads.
	 *
	 * @return void
	 */
	public function testTheMessageCarriesOnlyTheHost(): void {
		$this->store['zrcLocation'] = 'https://user:s3cret@zrc.gemeente.example/zaken/api/v1?token=abc';

		$message = $this->service()->observe(source: 'zrc', httpStatus: 200)[1];

		$this->assertStringContainsString(needle: 'zrc.gemeente.example', haystack: $message);
		foreach (['s3cret', 'user', 'token', 'abc', '/zaken'] as $leak) {
			$this->assertStringNotContainsString(needle: $leak, haystack: $message);
		}
	}//end testTheMessageCarriesOnlyTheHost()

	/**
	 * The same outcome reports once an hour, not on every call.
	 *
	 * @return void
	 */
	public function testTheSameStatusReportsOnceAnHour(): void {
		$service = $this->service();

		$this->assertTrue(condition: $service->reportCall(source: 'zrc', httpStatus: 200));
		$this->now += 600;
		$this->assertFalse(condition: $service->reportCall(source: 'zrc', httpStatus: 200));
		$this->now += (ConnectionReportService::REPEAT_SECONDS - 601);
		$this->assertFalse(condition: $service->reportCall(source: 'zrc', httpStatus: 204));
		$this->now += 1;
		$this->assertTrue(condition: $service->reportCall(source: 'zrc', httpStatus: 200));

		$this->assertCount(expectedCount: 2, haystack: $this->sent);
	}//end testTheSameStatusReportsOnceAnHour()

	/**
	 * A different outcome reports after five minutes, and not before.
	 *
	 * Two endpoints that disagree must not write a report on every request.
	 *
	 * @return void
	 */
	public function testAChangedStatusWaitsFiveMinutes(): void {
		$service = $this->service();

		$this->assertTrue(condition: $service->reportCall(source: 'zrc', httpStatus: 200));
		$this->now += (ConnectionReportService::CHANGE_SECONDS - 1);
		$this->assertFalse(condition: $service->reportCall(source: 'zrc', httpStatus: null));
		$this->now += 1;
		$this->assertTrue(condition: $service->reportCall(source: 'zrc', httpStatus: null));

		$this->assertSame(expected: ['configured', 'error'], actual: array_map(static fn (Event $event): string => $event->status, $this->sent));
	}//end testAChangedStatusWaitsFiveMinutes()

	/**
	 * Each source keeps its own memory.
	 *
	 * @return void
	 */
	public function testEachSourceKeepsItsOwnMemory(): void {
		$service = $this->service();

		$this->assertTrue(condition: $service->reportCall(source: 'zrc', httpStatus: 200));
		$this->assertTrue(condition: $service->reportCall(source: 'brc', httpStatus: 200));

		$this->assertSame(expected: ['zrc', 'brc'], actual: array_map(static fn (Event $event): string => $event->key, $this->sent));
	}//end testEachSourceKeepsItsOwnMemory()

	/**
	 * A source nothing declares as called is never reported.
	 *
	 * Integriq would refuse the report, and the six unused sources are decided
	 * by their declaration alone.
	 *
	 * @return void
	 */
	public function testAnUnreportedSourceIsIgnored(): void {
		$this->assertFalse(condition: $this->service()->reportCall(source: 'ztc', httpStatus: 200));
		$this->assertSame(expected: [], actual: $this->sent);
	}//end testAnUnreportedSourceIsIgnored()

	/**
	 * A save of a ZRC key refreshes ZRC, clears its memory, and leaves BRC alone.
	 *
	 * @return void
	 */
	public function testASaveRefreshesTheTouchedConnectionAndClearsItsMemory(): void {
		$service = $this->service();
		$service->reportCall(source: 'zrc', httpStatus: 200);
		$service->reportCall(source: 'brc', httpStatus: 200);
		$this->sent = [];

		$refreshed = $service->refreshFromSave(savedKeys: ['zrcKey', 'organisationName']);

		$this->assertSame(expected: ['zrc'], actual: $refreshed);
		$this->assertCount(expectedCount: 1, haystack: $this->sent);
		$this->assertInstanceOf(expected: ConnectionRefreshRequestedEvent::class, actual: $this->sent[0]);
		$this->assertSame(expected: 'zaakafhandelapp', actual: $this->sent[0]->app);
		$this->assertSame(expected: 'zrc', actual: $this->sent[0]->key);
		$this->assertArrayNotHasKey(key: 'connection_report_zrc', array: $this->store);
		$this->assertArrayHasKey(key: 'connection_report_brc', array: $this->store);

		// With the memory cleared the next call reports at once.
		$this->assertTrue(condition: $service->reportCall(source: 'zrc', httpStatus: 200));
	}//end testASaveRefreshesTheTouchedConnectionAndClearsItsMemory()

	/**
	 * A save that writes no ZRC or BRC key sends nothing.
	 *
	 * @return void
	 */
	public function testAnUnrelatedSaveSendsNoRefresh(): void {
		$this->assertSame(expected: [], actual: $this->service()->refreshFromSave(savedKeys: ['ztcLocation', 'organisationOIN']));
		$this->assertSame(expected: [], actual: $this->sent);
	}//end testAnUnrelatedSaveSendsNoRefresh()

	/**
	 * Without integriq nothing is sent, stored, cleared or logged.
	 *
	 * @return void
	 */
	public function testWithoutIntegriqNothingIsSentStoredOrLogged(): void {
		$this->dispatcher->expects($this->never())->method('dispatchTyped');
		$this->appConfig->expects($this->never())->method('setValueString');
		$this->appConfig->expects($this->never())->method('deleteKey');
		$this->logger->expects($this->never())->method('warning');

		$service = $this->serviceWithoutIntegriq();

		$this->assertFalse(condition: $service->reportCall(source: 'zrc', httpStatus: null));
		$this->assertSame(expected: [], actual: $service->refreshFromSave(savedKeys: ['zrcLocation']));
	}//end testWithoutIntegriqNothingIsSentStoredOrLogged()

	/**
	 * The class lookup answers null for a class nobody ships, and the class for a stub.
	 *
	 * This is the real guard, not the test double above.
	 *
	 * @return void
	 */
	public function testTheLookupAnswersNullForAnAbsentClass(): void {
		$method  = new ReflectionMethod(ConnectionReportService::class, 'resolveEventClass');
		$service = $this->service();

		$this->assertNull(actual: $method->invoke($service, 'OCA\\Nobody\\Event\\ShipsThisEvent'));
		$this->assertSame(
			expected: '\\' . ConnectionReportService::STATUS_EVENT,
			actual: $method->invoke($service, ConnectionReportService::STATUS_EVENT)
		);
	}//end testTheLookupAnswersNullForAnAbsentClass()

	/**
	 * The event names are the ones the contract fixes.
	 *
	 * A string class name is exactly the reference that rots into a silent
	 * no-op after a rename, so it is compared to the stubs' real names.
	 *
	 * @return void
	 */
	public function testTheEventNamesAreTheContractNames(): void {
		$this->assertSame(expected: ConnectionStatusReportedEvent::class, actual: ConnectionReportService::STATUS_EVENT);
		$this->assertSame(expected: ConnectionRefreshRequestedEvent::class, actual: ConnectionReportService::REFRESH_EVENT);
	}//end testTheEventNamesAreTheContractNames()

	/**
	 * A listener that throws never escapes, and leaves the memory unwritten.
	 *
	 * An unwritten memory means the next call tries again instead of keeping
	 * quiet for an hour about a report that never landed.
	 *
	 * @return void
	 */
	public function testAThrowingListenerNeverEscapes(): void {
		$dispatcher = $this->createMock(originalClassName: IEventDispatcher::class);
		$dispatcher->method('dispatchTyped')->willThrowException(new RuntimeException('registry down'));
		$this->logger->expects($this->exactly(count: 2))->method('warning')
			->with($this->stringContains(string: 'could not send'), $this->arrayHasKey(key: 'key'));

		$service = new ConnectionReportService(
			eventDispatcher: $dispatcher,
			appConfig: $this->appConfig,
			timeFactory: $this->createMock(originalClassName: ITimeFactory::class),
			logger: $this->logger,
		);

		$this->assertFalse(condition: $service->reportCall(source: 'zrc', httpStatus: 200));
		$this->assertSame(expected: [], actual: $service->refreshFromSave(savedKeys: ['brcLocation']));
		$this->assertArrayNotHasKey(key: 'connection_report_zrc', array: $this->store);
	}//end testAThrowingListenerNeverEscapes()

	/**
	 * A failing app config never escapes into the call.
	 *
	 * @return void
	 */
	public function testAFailingAppConfigNeverEscapes(): void {
		$appConfig = $this->createMock(originalClassName: IAppConfig::class);
		$appConfig->method('getValueString')->willThrowException(new RuntimeException('type conflict'));
		$this->logger->expects($this->once())->method('warning')
			->with($this->stringContains(string: 'could not report'), $this->arrayHasKey(key: 'exception'));

		$service = new ConnectionReportService(
			eventDispatcher: $this->dispatcher,
			appConfig: $appConfig,
			timeFactory: $this->createMock(originalClassName: ITimeFactory::class),
			logger: $this->logger,
		);

		$this->assertFalse(condition: $service->reportCall(source: 'zrc', httpStatus: 200));
		$this->assertSame(expected: [], actual: $this->sent);
	}//end testAFailingAppConfigNeverEscapes()
}//end class
