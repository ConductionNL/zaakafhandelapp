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
 * The audit endpoint carries the same IDOR guard as the per-resource trails:
 * ObjectService::getAuditTrail() takes only a uuid, so the getObject() call
 * above it (which goes through OpenRegister's RBAC) is the whole access check.
 * A 404 that still read the trail would defeat it, so "never read" is asserted
 * explicitly.
 */
class ObjectsControllerTest extends TestCase
{

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


    protected function setUp(): void
    {
        $this->objectService = $this->createMock(ObjectService::class);

    }//end setUp()


    /**
     * Build the controller under test.
     *
     * @param bool $authenticated Whether IUserSession returns a user.
     *
     * @return ObjectsController The controller under test.
     */
    private function makeController(bool $authenticated=true): ObjectsController
    {
        $request = $this->createMock(IRequest::class);
        $request->method('getParams')->willReturn([]);

        $session = $this->createMock(IUserSession::class);
        $session->method('getUser')->willReturn($authenticated === true ? $this->createMock(IUser::class) : null);

        return new ObjectsController('zaakafhandelapp', $request, $this->objectService, $session);

    }//end makeController()


    /**
     * Call the named graph endpoint on the given controller.
     *
     * Spelled out as a match rather than `$controller->{$method}()` so each
     * endpoint has a real, greppable call site — a dynamic dispatch reads as
     * "no test" to any tool that looks for one, including hydra gate-25.
     *
     * @param ObjectsController $controller The controller under test.
     * @param string            $method     The endpoint to call.
     * @param string            $objectType The object type to pass.
     *
     * @return JSONResponse The response returned.
     */
    private function invoke(ObjectsController $controller, string $method, string $objectType): JSONResponse
    {
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
    public static function graphEndpointProvider(): array
    {
        return [
            'audit'     => ['getAuditTrail'],
            'relations' => ['getRelations'],
            'uses'      => ['getUses'],
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
    public function testUnknownObjectTypeIsRejectedWithoutTouchingTheRegister(string $method): void
    {
        $this->objectService->expects($this->never())->method('getObject');
        $this->objectService->expects($this->never())->method('getAuditTrail');
        $this->objectService->expects($this->never())->method('getRelations');
        $this->objectService->expects($this->never())->method('getUses');

        $response = $this->invoke($this->makeController(), $method, self::FORBIDDEN_TYPE);

        $this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus(), $method.' status');
        $this->assertStringContainsString(
            self::FORBIDDEN_TYPE,
            (string) $response->getData()['error'],
            $method.' must name the rejected type'
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
    public function testUnauthenticatedGets401(string $method): void
    {
        $this->objectService->expects($this->never())->method('getObject');
        $this->objectService->expects($this->never())->method('getRelations');
        $this->objectService->expects($this->never())->method('getUses');

        $response = $this->invoke($this->makeController(false), $method, self::ALLOWED_TYPE);

        $this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus(), $method.' status');
    }//end testUnauthenticatedGets401()


    /**
     * getAuditTrail() resolves the object through the register (RBAC) before
     * reading the trail, and returns it verbatim with 200.
     *
     * @return void
     */
    public function testGetAuditTrailResolvesTheObjectThenReturnsTheTrail(): void
    {
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
    public function testGetAuditTrailAnswers404WithoutReadingTheTrail(): void
    {
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
    public function testGetRelationsReturnsTheIncomingReferences(): void
    {
        $relations = [['id' => 'zaak-1'], ['id' => 'zaak-2']];

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
    public function testGetRelationsTranslatesARegisterFailureInto400(): void
    {
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
    public function testGetUsesReturnsTheOutgoingReferences(): void
    {
        $uses = [['id' => 'zaaktype-1']];

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
}//end class
