<?php

/**
 * Wire-contract tests for the per-resource audit-trail endpoints.
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
use OCA\ZaakAfhandelApp\Controller\KlantenController;
use OCA\ZaakAfhandelApp\Controller\TakenController;
use OCA\ZaakAfhandelApp\Controller\ZakenController;
use OCA\ZaakAfhandelApp\Service\KlantContactSyncService;
use OCA\ZaakAfhandelApp\Service\MailService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Locks the wire contract of the four resource audit-trail endpoints:
 *
 *   GET /api/berichten/{id}/audit_trail  -> berichten#getAuditTrail
 *   GET /api/klanten/{id}/audit_trail    -> klanten#getAuditTrail
 *   GET /api/taken/{id}/audit_trail      -> taken#getAuditTrail
 *   GET /api/zaken/{id}/audit_trail      -> zaken#getAuditTrail
 *
 * These are #[NoAdminRequired] and the trail is fetched from OpenRegister by
 * bare uuid — ObjectService::getAuditTrail() takes no object type at all. The
 * ONLY thing standing between a non-admin caller and any object's history is
 * the getObject() resolution above it, which goes through OR's RBAC. Two
 * properties therefore have to hold on every one of the four, and neither is
 * observable from a 200 body:
 *
 *   1. the guard resolves the id against the controller's OWN object type, so
 *      a klant id cannot be used to read a zaak's trail;
 *   2. when the guard comes back empty the trail is NEVER read — a 404 that
 *      still called getAuditTrail() would have already leaked the history to
 *      whatever logging or caching sits under it.
 *
 * ControllerExceptionTranslationTest covers what these do when the service
 * throws; this file covers what they do when it does not.
 */
class AuditTrailEndpointsTest extends TestCase {

	/**
	 * The object id used as the route parameter throughout.
	 *
	 * @var string
	 */
	private const OBJECT_ID = 'object-7';

	/**
	 * @var ObjectService&MockObject
	 */
	private $objectService;

	protected function setUp(): void {
		$this->objectService = $this->createMock(ObjectService::class);

	}//end setUp()

	/**
	 * Build the named controller with the shared ObjectService mock.
	 *
	 * @param class-string $class Controller class to build.
	 * @param bool $authenticated Whether IUserSession returns a user.
	 *
	 * @return object The controller under test.
	 */
	private function makeController(string $class, bool $authenticated = true): object {
		$request = $this->createMock(IRequest::class);
		$request->method('getParams')->willReturn([]);

		$session = $this->createMock(IUserSession::class);
		$session->method('getUser')->willReturn($authenticated === true ? $this->createMock(IUser::class) : null);

		$logger = $this->createMock(LoggerInterface::class);

		if ($class === TakenController::class) {
			return new TakenController(
				'zaakafhandelapp',
				$request,
				$this->createMock(MailService::class),
				$this->objectService,
				$session,
				$logger
			);
		}

		if ($class === KlantenController::class) {
			return new KlantenController(
				'zaakafhandelapp',
				$request,
				$this->objectService,
				$session,
				$this->createMock(KlantContactSyncService::class),
				$logger
			);
		}

		return new $class('zaakafhandelapp', $request, $this->objectService, $session, $logger);
	}//end makeController()

	/**
	 * The four controllers and the object type each must resolve the id against.
	 *
	 * @return array<string, array{0: class-string, 1: string}>
	 */
	public static function auditTrailProvider(): array {
		return [
			'berichten' => [BerichtenController::class, 'berichten'],
			'klanten' => [KlantenController::class, 'klanten'],
			'taken' => [TakenController::class, 'taken'],
			'zaken' => [ZakenController::class, 'zaken'],
		];
	}//end auditTrailProvider()

	/**
	 * The happy path: 200, the trail verbatim, and the guard resolved against
	 * the controller's own object type.
	 *
	 * @param class-string $class Controller under test.
	 * @param string $objectType The object type the guard must use.
	 *
	 * @return void
	 */
	#[DataProvider('auditTrailProvider')]
	public function testReturnsTheTrailAfterResolvingItsOwnObjectType(string $class, string $objectType): void {
		$trail = [
			['id' => 'log-1', 'action' => 'create', 'created' => '2026-06-14T10:00:00Z'],
			['id' => 'log-2', 'action' => 'update', 'created' => '2026-06-15T11:00:00Z'],
		];

		$this->objectService->expects($this->once())
			->method('getObject')
			->with($objectType, self::OBJECT_ID)
			->willReturn(['id' => self::OBJECT_ID]);
		$this->objectService->expects($this->once())
			->method('getAuditTrail')
			->with(self::OBJECT_ID)
			->willReturn($trail);

		$response = $this->makeController($class)->getAuditTrail(self::OBJECT_ID);

		$this->assertSame(Http::STATUS_OK, $response->getStatus(), $class . ' status');
		$this->assertSame($trail, $response->getData(), $class . ' body');
	}//end testReturnsTheTrailAfterResolvingItsOwnObjectType()

	/**
	 * An id the caller cannot resolve answers 404 and the trail is never read.
	 *
	 * @param class-string $class Controller under test.
	 *
	 * @return void
	 */
	#[DataProvider('auditTrailProvider')]
	public function testUnresolvableIdAnswers404AndNeverReadsTheTrail(string $class): void {
		$this->objectService->method('getObject')->willReturn(null);
		$this->objectService->expects($this->never())->method('getAuditTrail');

		$response = $this->makeController($class)->getAuditTrail(self::OBJECT_ID);

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus(), $class . ' status');
		$this->assertArrayHasKey('error', $response->getData(), $class . ' body');
	}//end testUnresolvableIdAnswers404AndNeverReadsTheTrail()

	/**
	 * An unauthenticated caller gets 401 before the object is even resolved.
	 *
	 * @param class-string $class Controller under test.
	 *
	 * @return void
	 */
	#[DataProvider('auditTrailProvider')]
	public function testUnauthenticatedGets401BeforeResolvingAnything(string $class): void {
		$this->objectService->expects($this->never())->method('getObject');
		$this->objectService->expects($this->never())->method('getAuditTrail');

		$response = $this->makeController($class, false)->getAuditTrail(self::OBJECT_ID);

		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus(), $class . ' status');
	}//end testUnauthenticatedGets401BeforeResolvingAnything()
}//end class
