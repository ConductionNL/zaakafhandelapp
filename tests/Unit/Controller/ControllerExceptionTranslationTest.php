<?php

/**
 * Unit tests for the controller exception-translation contract (gate-49).
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Controller
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

namespace OCA\ZaakAfhandelApp\Tests\Unit\Controller;

use OCA\ZaakAfhandelApp\Controller\BerichtenController;
use OCA\ZaakAfhandelApp\Controller\ContactMomentenController;
use OCA\ZaakAfhandelApp\Controller\KlantenController;
use OCA\ZaakAfhandelApp\Controller\MedewerkersController;
use OCA\ZaakAfhandelApp\Controller\TakenController;
use OCA\ZaakAfhandelApp\Controller\ZaakTypenController;
use OCA\ZaakAfhandelApp\Controller\ZakenController;
use OCA\ZaakAfhandelApp\Service\KlantContactSyncService;
use OCA\ZaakAfhandelApp\Service\MailService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Locks the exception-translation contract on the seven ZGW CRUD controllers.
 *
 * Every one of the 32 methods covered here calls a service method that can
 * throw: ObjectQueryService::saveObject() hands the payload to the OpenRegister
 * mapper's updateFromArray(), which raises DoesNotExistException for an unknown
 * id, and getMapper()/assertExtendAllowed() raise too. Before the translation
 * these were uncaught — an HTTP 500 with a stack trace on a route marked
 * #[NoAdminRequired], reachable by any authenticated non-admin.
 *
 * The contract asserted here is three-part:
 *   1. a DoesNotExistException becomes a 404 (except on create(), which has no
 *      id to miss and answers 400 like any other bad payload);
 *   2. any other Throwable becomes a translated JSON error — 400 on write
 *      paths, 500 on read paths — and is LOGGED, not swallowed;
 *   3. the response body never carries the exception text.
 *
 * Part 3 is the one worth stating out loud: these are #[NoAdminRequired]
 * routes, so an exception message echoed into the body is an information
 * disclosure to a non-admin. The tests assert the secret string never appears.
 */
class ControllerExceptionTranslationTest extends TestCase {

	/**
	 * The message the thrown exception carries. Asserted ABSENT from every
	 * response body — if it ever appears, the controller is echoing internals.
	 *
	 * @var string
	 */
	private const SECRET = 'SECRET-INTERNAL-DETAIL-8f21';

	/**
	 * @var ObjectService&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $objectService;

	/**
	 * @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject
	 */
	private $logger;

	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);
		$this->logger = $this->createMock(LoggerInterface::class);
	}//end setUp()

	/**
	 * Build the named controller with the shared mocks.
	 *
	 * @param string $class Fully-qualified controller class name.
	 *
	 * @return object The constructed controller.
	 */
	private function makeController(string $class): object {
		$request = $this->createMock(IRequest::class);
		$request->method('getParams')->willReturn(['title' => 'x']);

		$session = $this->createMock(IUserSession::class);
		$session->method('getUser')->willReturn($this->createMock(IUser::class));

		if ($class === TakenController::class) {
			return new TakenController(
				'zaakafhandelapp',
				$request,
				$this->createMock(MailService::class),
				$this->objectService,
				$session,
				$this->logger
			);
		}

		if ($class === KlantenController::class) {
			return new KlantenController(
				'zaakafhandelapp',
				$request,
				$this->objectService,
				$session,
				$this->createMock(KlantContactSyncService::class),
				$this->logger
			);
		}

		return new $class('zaakafhandelapp', $request, $this->objectService, $session, $this->logger);
	}//end makeController()

	/**
	 * Make every data-touching ObjectService method throw the given exception.
	 *
	 * @param \Throwable $e The exception to raise.
	 *
	 * @return void
	 */
	private function throwFromEveryServiceCall(\Throwable $e): void {
		foreach (['getObject', 'saveObject', 'deleteObject', 'getAuditTrail'] as $method) {
			$this->objectService->method($method)->willThrowException($e);
		}
	}//end throwFromEveryServiceCall()

	/**
	 * Invoke a controller method with the arity it declares.
	 *
	 * @param object $controller The controller under test.
	 * @param string $method The method name.
	 *
	 * @return JSONResponse The response returned.
	 */
	private function call(object $controller, string $method): JSONResponse {
		// create() takes no id; every other method under test takes one.
		return $method === 'create' ? $controller->create() : $controller->{$method}('unknown-id');
	}//end call()

	/**
	 * Every (controller, method) pair gate-49 reported, with the status each
	 * must answer for a DoesNotExistException and for any other Throwable.
	 *
	 * create() carries no id, so it has no DoesNotExistException arm and
	 * answers 400 for both — that asymmetry is deliberate and is pinned here.
	 *
	 * @return array<string, array{0: class-string, 1: string, 2: int, 3: int}>
	 */
	public static function methodProvider(): array {
		$cases = [];
		$suites = [
			'berichten' => [BerichtenController::class, ['show', 'create', 'update', 'destroy', 'getAuditTrail']],
			'contactmomenten' => [ContactMomentenController::class, ['show', 'create', 'update', 'destroy']],
			'klanten' => [KlantenController::class, ['show', 'create', 'update', 'destroy', 'getAuditTrail']],
			'medewerkers' => [MedewerkersController::class, ['show', 'create', 'update', 'destroy']],
			'taken' => [TakenController::class, ['show', 'create', 'update', 'destroy', 'getAuditTrail']],
			'zaaktypen' => [ZaakTypenController::class, ['show', 'create', 'update', 'destroy']],
			'zaken' => [ZakenController::class, ['show', 'create', 'update', 'destroy', 'getAuditTrail']],
		];

		// Status a generic Throwable must translate to, per method kind:
		// writes answer 400 (the payload is the likeliest cause), reads 500.
		$genericStatus = [
			'show' => Http::STATUS_INTERNAL_SERVER_ERROR,
			'create' => Http::STATUS_BAD_REQUEST,
			'update' => Http::STATUS_BAD_REQUEST,
			'destroy' => Http::STATUS_INTERNAL_SERVER_ERROR,
			'getAuditTrail' => Http::STATUS_INTERNAL_SERVER_ERROR,
		];

		foreach ($suites as $label => [$class, $methods]) {
			foreach ($methods as $method) {
				$notFoundStatus = $method === 'create' ? Http::STATUS_BAD_REQUEST : Http::STATUS_NOT_FOUND;

				$cases[$label . '::' . $method] = [$class, $method, $notFoundStatus, $genericStatus[$method]];
			}
		}

		return $cases;
	}//end methodProvider()

	/**
	 * A DoesNotExistException must never reach the framework as a 500.
	 *
	 * @param class-string $class Controller under test.
	 * @param string $method Method under test.
	 * @param int $expectedStatus Status the method must answer.
	 *
	 * @return void
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider('methodProvider')]
	public function testDoesNotExistExceptionIsTranslated(string $class, string $method, int $expectedStatus): void {
		$this->throwFromEveryServiceCall(new DoesNotExistException(self::SECRET));

		$response = $this->call($this->makeController($class), $method);

		$this->assertSame($expectedStatus, $response->getStatus(), $class . '::' . $method . ' status');
		$this->assertStringNotContainsString(
			self::SECRET,
			json_encode($response->getData()),
			$class . '::' . $method . ' must not echo the exception text'
		);
	}//end testDoesNotExistExceptionIsTranslated()

	/**
	 * Any other Throwable must be translated AND logged — never swallowed and
	 * never rendered as a framework stack trace.
	 *
	 * @param class-string $class Controller under test.
	 * @param string $method Method under test.
	 * @param int $unusedNotFound Unused here; supplied by the shared provider.
	 * @param int $expectedGeneric Status the method must answer.
	 *
	 * @return void
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider('methodProvider')]
	public function testGenericThrowableIsTranslatedAndLogged(
		string $class,
		string $method,
		int $unusedNotFound,
		int $expectedGeneric,
	): void {
		$this->throwFromEveryServiceCall(new \RuntimeException(self::SECRET));

		// Swallowing is the failure mode this asserts against: the cause must
		// reach the log even though it does not reach the caller.
		$this->logger->expects($this->once())->method('error');

		$response = $this->call($this->makeController($class), $method);

		$this->assertSame($expectedGeneric, $response->getStatus(), $class . '::' . $method . ' status');
		$this->assertStringNotContainsString(
			self::SECRET,
			json_encode($response->getData()),
			$class . '::' . $method . ' must not echo the exception text'
		);
	}//end testGenericThrowableIsTranslatedAndLogged()

	/**
	 * An \Error (not an \Exception) must be translated too.
	 *
	 * This is not academic: TakenController::update() called
	 * $oldObject->jsonSerialize() for the mail diff, and getObject() returns
	 * null for an unknown id — a fatal Error, which a `catch (\Exception)`
	 * would have walked straight past.
	 *
	 * @return void
	 */
	public function testErrorIsTranslatedNotOnlyException(): void {
		$this->throwFromEveryServiceCall(new \Error(self::SECRET));

		$response = $this->call($this->makeController(ZakenController::class), 'show');

		$this->assertSame(Http::STATUS_INTERNAL_SERVER_ERROR, $response->getStatus());
		$this->assertStringNotContainsString(self::SECRET, json_encode($response->getData()));
	}//end testErrorIsTranslatedNotOnlyException()

	/**
	 * TakenController::update() answers 404 for an unknown id instead of
	 * dereferencing null.
	 *
	 * getObject() returns null (it does not throw) when the mapper reports the
	 * object missing, and the mail diff below it called
	 * $oldObject->jsonSerialize() unguarded. That was a fatal Error on the
	 * plainest possible request: PUT with an id that is not there.
	 *
	 * @return void
	 */
	public function testTakenUpdateAnswers404ForUnknownIdInsteadOfDereferencingNull(): void {
		$this->objectService->method('getObject')->willReturn(null);
		$this->objectService->expects($this->never())->method('saveObject');

		$response = $this->call($this->makeController(TakenController::class), 'update');

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}//end testTakenUpdateAnswers404ForUnknownIdInsteadOfDereferencingNull()

	/**
	 * The audit-trail IDOR guard still answers 404 on a null object, and does
	 * not go on to read the trail.
	 *
	 * @return void
	 */
	public function testAuditTrailGuardAnswers404WithoutReadingTheTrail(): void {
		$this->objectService->method('getObject')->willReturn(null);
		$this->objectService->expects($this->never())->method('getAuditTrail');

		$response = $this->call($this->makeController(ZakenController::class), 'getAuditTrail');

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
	}//end testAuditTrailGuardAnswers404WithoutReadingTheTrail()

	/**
	 * destroy() answers 404 when the service reports nothing was deleted.
	 *
	 * The methods used to return the bare integers 200/404; this pins the
	 * Http::STATUS_* constants that replaced them.
	 *
	 * @return void
	 */
	public function testDestroyAnswers404WhenNothingWasDeleted(): void {
		$this->objectService->method('deleteObject')->willReturn(false);

		$response = $this->call($this->makeController(ZakenController::class), 'destroy');

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
		$this->assertFalse($response->getData()['success']);
	}//end testDestroyAnswers404WhenNothingWasDeleted()

	/**
	 * destroy() answers 200 on a successful delete.
	 *
	 * @return void
	 */
	public function testDestroyAnswers200OnSuccess(): void {
		$this->objectService->method('deleteObject')->willReturn(true);

		$response = $this->call($this->makeController(ZakenController::class), 'destroy');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertTrue($response->getData()['success']);
	}//end testDestroyAnswers200OnSuccess()

	/**
	 * An unauthenticated caller is rejected before any service call — the
	 * guard must not be shadowed by the new try/catch.
	 *
	 * @return void
	 */
	public function testUnauthenticatedIsRejectedBeforeAnyServiceCall(): void {
		$request = $this->createMock(IRequest::class);
		$request->method('getParams')->willReturn([]);

		$session = $this->createMock(IUserSession::class);
		$session->method('getUser')->willReturn(null);

		$this->objectService->expects($this->never())->method('getObject');

		$controller = new ZakenController('zaakafhandelapp', $request, $this->objectService, $session, $this->logger);

		$this->assertSame(Http::STATUS_UNAUTHORIZED, $controller->show('any-id')->getStatus());
	}//end testUnauthenticatedIsRejectedBeforeAnyServiceCall()
}//end class
