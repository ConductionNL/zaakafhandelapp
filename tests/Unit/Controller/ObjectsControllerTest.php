<?php

/**
 * Wire-contract tests for the generic objects graph endpoints.
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

use OCA\ZaakAfhandelApp\Controller\ObjectsController;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Locks the wire contract of the three generic object graph endpoints:
 *
 *   GET api/objects/{objectType}/{id}/audit      -> objects#getAuditTrail
 *   GET api/objects/{objectType}/{id}/relations  -> objects#getRelations
 *   GET api/objects/{objectType}/{id}/uses       -> objects#getUses
 *
 * All three are #[NoAdminRequired] and take the object type straight off the
 * URL, which is exactly the shape issue #276 was filed about: an unvalidated
 * objectType reaches the register and can name a schema the app never meant to
 * expose. ALLOWED_OBJECT_TYPES is the mitigation, and a 400 for an unknown type
 * is therefore a security contract, not a validation nicety — so it is asserted
 * per endpoint rather than once for the class.
 *
 * All three graph endpoints resolve the object through the type's own mapper
 * before touching the graph. That is a SCOPE guard, not an authorisation guard:
 * OpenRegister resolves relations, uses and audit rows from the uuid alone, so
 * without it an id belonging to an entirely different register can be walked
 * through a route for a type it has nothing to do with (measured live: HTTP 200
 * returning another register's object). It does NOT establish that the caller
 * may see the object — OR's RBAC returns true for a schema with an empty
 * `authorization` block and this app ships none (see zaakafhandelapp#347).
 * An earlier version of this docblock said the resolve "goes through
 * OpenRegister's RBAC"; it does not, and that sentence is the kind of false
 * assurance a reviewer marks as done.
 *
 * The write verbs additionally re-apply the app's OWN admin-only decision for
 * master data (klanten, zaaktypen), which KlantenController and
 * ZaakTypenController enforce by omitting @NoAdminRequired and which this
 * generic route otherwise bypasses.
 */
class ObjectsControllerTest extends TestCase {

	/**
	 * A type that is on ALLOWED_OBJECT_TYPES.
	 *
	 * @var string
	 */
	private const ALLOWED_TYPE = 'zaken';

	/**
	 * A type that is NOT on ALLOWED_OBJECT_TYPES.
	 *
	 * @var string
	 */
	private const FORBIDDEN_TYPE = 'configuration';

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
	 * Build the controller under test.
	 *
	 * @param bool $authenticated Whether IUserSession returns a user.
	 * @param bool $isAdmin Whether that user is an administrator.
	 * @param array<string,mixed> $params The request parameters to serve.
	 *
	 * @return ObjectsController The controller under test.
	 */
	private function makeController(bool $authenticated = true, bool $isAdmin = false, array $params = []): ObjectsController {
		$request = $this->createMock(IRequest::class);
		$request->method('getParams')->willReturn($params);

		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('caseworker-1');

		$session = $this->createMock(IUserSession::class);
		$session->method('getUser')->willReturn($authenticated === true ? $user : null);

		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('isAdmin')->willReturn($isAdmin);

		return new ObjectsController('zaakafhandelapp', $request, $this->objectService, $session, $groupManager);
	}//end makeController()

	/**
	 * Call the named graph endpoint on the given controller.
	 *
	 * Spelled out as a match rather than `$controller->{$method}()` so each
	 * endpoint has a real, greppable call site — a dynamic dispatch reads as
	 * "no test" to any tool that looks for one, including hydra gate-25.
	 *
	 * @param ObjectsController $controller The controller under test.
	 * @param string $method The endpoint to call.
	 * @param string $objectType The object type to pass.
	 *
	 * @return JSONResponse The response returned.
	 */
	private function invoke(ObjectsController $controller, string $method, string $objectType): JSONResponse {
		return match ($method) {
			'getAuditTrail' => $controller->getAuditTrail($objectType, self::OBJECT_ID),
			'getRelations' => $controller->getRelations($objectType, self::OBJECT_ID),
			'getUses' => $controller->getUses($objectType, self::OBJECT_ID),
		};

	}//end invoke()

	/**
	 * The three graph endpoints under test.
	 *
	 * @return array<string, array{0: string}>
	 */
	public static function graphEndpointProvider(): array {
		return [
			'audit' => ['getAuditTrail'],
			'relations' => ['getRelations'],
			'uses' => ['getUses'],
		];
	}//end graphEndpointProvider()

	/**
	 * An objectType outside the allow-list is rejected with 400 and never
	 * reaches the register (#276).
	 *
	 * @param string $method The controller method under test.
	 *
	 * @return void
	 */
	#[DataProvider('graphEndpointProvider')]
	public function testUnknownObjectTypeIsRejectedWithoutTouchingTheRegister(string $method): void {
		$this->objectService->expects($this->never())->method('getObject');
		$this->objectService->expects($this->never())->method('getAuditTrail');
		$this->objectService->expects($this->never())->method('getRelations');
		$this->objectService->expects($this->never())->method('getUses');

		$response = $this->invoke($this->makeController(), $method, self::FORBIDDEN_TYPE);

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus(), $method . ' status');
		$this->assertStringContainsString(
			self::FORBIDDEN_TYPE,
			(string)$response->getData()['error'],
			$method . ' must name the rejected type'
		);
	}//end testUnknownObjectTypeIsRejectedWithoutTouchingTheRegister()

	/**
	 * An unauthenticated caller gets 401 before the type is even validated.
	 *
	 * @param string $method The controller method under test.
	 *
	 * @return void
	 */
	#[DataProvider('graphEndpointProvider')]
	public function testUnauthenticatedGets401(string $method): void {
		$this->objectService->expects($this->never())->method('getObject');
		$this->objectService->expects($this->never())->method('getRelations');
		$this->objectService->expects($this->never())->method('getUses');

		$response = $this->invoke($this->makeController(false), $method, self::ALLOWED_TYPE);

		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus(), $method . ' status');
	}//end testUnauthenticatedGets401()

	/**
	 * getAuditTrail() resolves the object through the type's own mapper before
	 * reading the trail, and returns it verbatim with 200.
	 *
	 * @return void
	 */
	public function testGetAuditTrailResolvesTheObjectThenReturnsTheTrail(): void {
		$trail = [['id' => 'log-1', 'action' => 'update']];

		$this->objectService->expects($this->once())
			->method('getObject')
			->with(self::ALLOWED_TYPE, self::OBJECT_ID)
			->willReturn(['id' => self::OBJECT_ID]);
		$this->objectService->expects($this->once())
			->method('getAuditTrail')
			->with(self::OBJECT_ID)
			->willReturn($trail);

		$response = $this->makeController()->getAuditTrail(self::ALLOWED_TYPE, self::OBJECT_ID);

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame($trail, $response->getData());
	}//end testGetAuditTrailResolvesTheObjectThenReturnsTheTrail()

	/**
	 * An object the caller cannot resolve answers 404 and the trail is never
	 * read.
	 *
	 * @return void
	 */
	public function testGetAuditTrailAnswers404WithoutReadingTheTrail(): void {
		$this->objectService->method('getObject')->willReturn(null);
		$this->objectService->expects($this->never())->method('getAuditTrail');

		$response = $this->makeController()->getAuditTrail(self::ALLOWED_TYPE, self::OBJECT_ID);

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
		$this->assertArrayHasKey('error', $response->getData());
	}//end testGetAuditTrailAnswers404WithoutReadingTheTrail()

	/**
	 * getRelations() returns the incoming references for the routed id, 200.
	 *
	 * @return void
	 */
	public function testGetRelationsReturnsTheIncomingReferences(): void {
		$relations = [['id' => 'zaak-1'], ['id' => 'zaak-2']];

		$this->objectService->method('getObject')->willReturn(['id' => self::OBJECT_ID]);
		$this->objectService->expects($this->once())
			->method('getRelations')
			->with(self::OBJECT_ID)
			->willReturn($relations);

		$response = $this->makeController()->getRelations(self::ALLOWED_TYPE, self::OBJECT_ID);

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame($relations, $response->getData());
	}//end testGetRelationsReturnsTheIncomingReferences()

	/**
	 * getRelations() translates a register failure into a 400 rather than
	 * letting it surface as a framework 500 on a #[NoAdminRequired] route.
	 *
	 * @return void
	 */
	public function testGetRelationsTranslatesARegisterFailureInto400(): void {
		$this->objectService->method('getObject')->willReturn(['id' => self::OBJECT_ID]);
		$this->objectService->method('getRelations')->willThrowException(new \RuntimeException('register down'));

		$response = $this->makeController()->getRelations(self::ALLOWED_TYPE, self::OBJECT_ID);

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$this->assertArrayHasKey('error', $response->getData());
	}//end testGetRelationsTranslatesARegisterFailureInto400()

	/**
	 * getUses() returns the outgoing references for the routed id, 200.
	 *
	 * An empty result is a legitimate answer here (an object that points at
	 * nothing), so the empty case is pinned too — it must be `[]` and 200, not
	 * a 404.
	 *
	 * @return void
	 */
	public function testGetUsesReturnsTheOutgoingReferences(): void {
		$uses = [['id' => 'zaaktype-1']];

		$this->objectService->method('getObject')->willReturn(['id' => self::OBJECT_ID]);
		$this->objectService->expects($this->exactly(2))
			->method('getUses')
			->with(self::OBJECT_ID)
			->willReturnOnConsecutiveCalls($uses, []);

		$controller = $this->makeController();

		$populated = $controller->getUses(self::ALLOWED_TYPE, self::OBJECT_ID);
		$this->assertSame(Http::STATUS_OK, $populated->getStatus());
		$this->assertSame($uses, $populated->getData());

		$empty = $controller->getUses(self::ALLOWED_TYPE, self::OBJECT_ID);
		$this->assertSame(Http::STATUS_OK, $empty->getStatus());
		$this->assertSame([], $empty->getData());
	}//end testGetUsesReturnsTheOutgoingReferences()

	/**
	 * An id that does not resolve inside the requested type answers 404 and the
	 * graph is never walked.
	 *
	 * This is the cross-register read closed by scoping the mapper: OpenRegister
	 * resolves relations, uses and audit rows from the uuid alone, so an id from
	 * another register would otherwise be answered here. Asserting "never
	 * called" rather than only the status is deliberate — a 404 that had already
	 * read the graph would still have leaked it into the log and the timing.
	 *
	 * @param string $method The controller method under test.
	 *
	 * @return void
	 */
	#[DataProvider('graphEndpointProvider')]
	public function testGraphEndpointsAnswer404ForAnIdOutsideTheType(string $method): void {
		$this->objectService->method('getObject')->willReturn(null);
		$this->objectService->expects($this->never())->method('getAuditTrail');
		$this->objectService->expects($this->never())->method('getRelations');
		$this->objectService->expects($this->never())->method('getUses');

		$response = $this->invoke($this->makeController(), $method, self::ALLOWED_TYPE);

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus(), $method . ' status');
	}//end testGraphEndpointsAnswer404ForAnIdOutsideTheType()

	/**
	 * ::show() answers 404 — not 200 with a null body — for an id that does not
	 * resolve inside the requested type.
	 *
	 * @return void
	 */
	public function testShowAnswers404ForAnIdOutsideTheType(): void {
		$this->objectService->method('getObject')->willReturn(null);

		$response = $this->makeController()->show(self::ALLOWED_TYPE, self::OBJECT_ID);

		$this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
		$this->assertArrayHasKey('error', $response->getData());
	}//end testShowAnswers404ForAnIdOutsideTheType()

	/**
	 * The object types whose writes this app has already declared admin-only,
	 * paired with the write verb under test.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function masterDataWriteProvider(): array {
		return [
			'create klanten' => ['create', 'klanten'],
			'update klanten' => ['update', 'klanten'],
			'destroy klanten' => ['destroy', 'klanten'],
			'create zaaktypen' => ['create', 'zaaktypen'],
			'update zaaktypen' => ['update', 'zaaktypen'],
			'destroy zaaktypen' => ['destroy', 'zaaktypen'],
		];
	}//end masterDataWriteProvider()

	/**
	 * Call a write verb on the controller.
	 *
	 * @param ObjectsController $controller The controller under test.
	 * @param string $verb create|update|destroy.
	 * @param string $objectType The object type to pass.
	 *
	 * @return JSONResponse The response returned.
	 */
	private function invokeWrite(ObjectsController $controller, string $verb, string $objectType): JSONResponse {
		return match ($verb) {
			'create' => $controller->create($objectType),
			'update' => $controller->update($objectType, self::OBJECT_ID),
			'destroy' => $controller->destroy($objectType, self::OBJECT_ID),
		};
	}//end invokeWrite()

	/**
	 * A non-admin writing master data through the generic route gets 403 and the
	 * register is never written.
	 *
	 * Measured live before this guard existed, one non-admin account, same
	 * operation, two routes:
	 *   DELETE api/klanten/{id}           -> HTTP 403
	 *   DELETE api/objects/klanten/{id}   -> HTTP 200 {"success":true}, deleted
	 *
	 * @param string $verb The write verb under test.
	 * @param string $objectType The master-data type under test.
	 *
	 * @return void
	 */
	#[DataProvider('masterDataWriteProvider')]
	public function testMasterDataWritesAreRefusedForNonAdmins(string $verb, string $objectType): void {
		$this->objectService->expects($this->never())->method('saveObject');
		$this->objectService->expects($this->never())->method('deleteObject');

		$response = $this->invokeWrite($this->makeController(true, false), $verb, $objectType);

		$this->assertSame(Http::STATUS_FORBIDDEN, $response->getStatus(), $verb . ' ' . $objectType . ' status');
		$this->assertStringContainsString(
			$objectType,
			(string)$response->getData()['error'],
			$verb . ' ' . $objectType . ' must name the refused type'
		);
	}//end testMasterDataWritesAreRefusedForNonAdmins()

	/**
	 * The same writes still succeed for an administrator — the guard is a
	 * restriction, not a blanket refusal that would read as "fixed" while
	 * breaking the feature.
	 *
	 * @param string $verb The write verb under test.
	 * @param string $objectType The master-data type under test.
	 *
	 * @return void
	 */
	#[DataProvider('masterDataWriteProvider')]
	public function testMasterDataWritesStillSucceedForAdmins(string $verb, string $objectType): void {
		$this->objectService->method('saveObject')->willReturn(['id' => self::OBJECT_ID]);
		$this->objectService->method('deleteObject')->willReturn(true);

		$response = $this->invokeWrite($this->makeController(true, true), $verb, $objectType);

		$this->assertNotSame(Http::STATUS_FORBIDDEN, $response->getStatus(), $verb . ' ' . $objectType . ' status');
	}//end testMasterDataWritesStillSucceedForAdmins()

	/**
	 * A type the app has NOT declared admin-only is still writable by an
	 * ordinary caseworker — the guard must not quietly widen to the whole
	 * allow-list.
	 *
	 * @return void
	 */
	public function testNonMasterDataWritesAreStillAllowedForNonAdmins(): void {
		$this->objectService->method('saveObject')->willReturn(['id' => self::OBJECT_ID]);
		$this->objectService->method('deleteObject')->willReturn(true);

		$controller = $this->makeController(true, false);

		$this->assertNotSame(
			Http::STATUS_FORBIDDEN,
			$controller->create(self::ALLOWED_TYPE)->getStatus(),
			'create zaken'
		);
		$this->assertNotSame(
			Http::STATUS_FORBIDDEN,
			$controller->update(self::ALLOWED_TYPE, self::OBJECT_ID)->getStatus(),
			'update zaken'
		);
		$this->assertNotSame(
			Http::STATUS_FORBIDDEN,
			$controller->destroy(self::ALLOWED_TYPE, self::OBJECT_ID)->getStatus(),
			'destroy zaken'
		);
	}//end testNonMasterDataWritesAreStillAllowedForNonAdmins()
}//end class
