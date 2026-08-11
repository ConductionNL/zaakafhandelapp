<?php

/**
 * Unit tests for ZaakAuditTrailController.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Controller
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Controller;

use OCA\ZaakAfhandelApp\Controller\ZaakAuditTrailController;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Locks the ZRC audit-trail contract: OR audit entries mapped onto the ZGW
 * Audittrail shape (actie mapping + wijzigingen oud/nieuw), single-entry
 * lookup by uuid, the read-only 405 (Allow: GET) on write verbs, and the 401
 * unauthenticated guard on all verbs.
 */
class ZaakAuditTrailControllerTest extends TestCase
{

    /**
     * @var ObjectService&MockObject
     */
    private $objectService;

    /**
     * @var ZaakAuditTrailController
     */
    private $controller;

    private const ZAAK = 'zaak-1';

    /**
     * What ObjectService::getObject('zaken', …) resolves to for the test at hand.
     * `null` models an id that does not exist inside this app's register.
     *
     * @var array<string,mixed>|null
     */
    private ?array $resolvedZaak = ['id' => self::ZAAK];


    protected function setUp(): void
    {
        $this->objectService = $this->createMock(ObjectService::class);

        // The zaak resolves by default. Driven through a property rather than a
        // second willReturn() because PHPUnit keeps the FIRST matching invocation
        // rule, so a per-test re-stub of the same method would silently be ignored
        // and the scope-guard tests would pass for the wrong reason.
        $this->objectService->method('getObject')->willReturnCallback(
            fn (): ?array => $this->resolvedZaak
        );

        $urlGenerator = $this->createMock(IURLGenerator::class);
        $urlGenerator->method('getAbsoluteURL')->willReturnArgument(0);

        $session = $this->createMock(IUserSession::class);
        $session->method('getUser')->willReturn($this->createMock(IUser::class));

        $this->controller = new ZaakAuditTrailController(
            'zaakafhandelapp',
            $this->createMock(IRequest::class),
            $this->objectService,
            $urlGenerator,
            $session
        );
    }//end setUp()


    public function testIndexMapsOrEntriesToZgwAudittrailShape(): void
    {
        $this->objectService->method('getAuditTrail')
            ->with(self::ZAAK)
            ->willReturn(
                [
                    ['uuid' => 'e1', 'action' => 'create', 'created' => '2026-06-14T10:00:00Z'],
                    ['uuid' => 'e2', 'action' => 'update', 'changed' => ['old' => ['a' => 1], 'new' => ['a' => 2]]],
                ]
            );

        $data = $this->controller->index(self::ZAAK)->getData();

        $this->assertCount(2, $data['results']);
        $this->assertSame('create', $data['results'][0]['actie']);
        $this->assertSame('ZRC', $data['results'][0]['bron']);
        $this->assertStringContainsString(self::ZAAK, (string) $data['results'][0]['hoofdObject']);
        $this->assertSame('update', $data['results'][1]['actie']);
        $this->assertSame(['a' => 1], $data['results'][1]['wijzigingen']['oud']);
        $this->assertSame(['a' => 2], $data['results'][1]['wijzigingen']['nieuw']);
    }//end testIndexMapsOrEntriesToZgwAudittrailShape()


    public function testShowReturnsSingleEntryByUuid(): void
    {
        $this->objectService->method('getAuditTrail')->willReturn(
            [['uuid' => 'e1', 'action' => 'create'], ['uuid' => 'e2', 'action' => 'update']]
        );

        $response = $this->controller->show(self::ZAAK, 'e2');

        $this->assertSame(Http::STATUS_OK, $response->getStatus());
        $this->assertSame('e2', $response->getData()['uuid']);
    }//end testShowReturnsSingleEntryByUuid()


    public function testShowReturns404ForUnknownEntry(): void
    {
        $this->objectService->method('getAuditTrail')->willReturn([['uuid' => 'e1', 'action' => 'create']]);

        $this->assertSame(Http::STATUS_NOT_FOUND, $this->controller->show(self::ZAAK, 'missing')->getStatus());
    }//end testShowReturns404ForUnknownEntry()


    /**
     * An id that does not resolve as a zaak in this app's register answers 404
     * and the trail is never read.
     *
     * Measured live before this guard existed:
     * `GET api/zrc/zaken/{uuid-of-an-object-in-another-register}/audit_trail`
     * returned **HTTP 200 with that object's audit row**, because
     * ObjectService::getAuditTrail() resolves rows from the uuid alone. The
     * sibling per-resource trails already had this check; these two did not.
     *
     * @return void
     */
    public function testIndexAnswers404ForAnIdOutsideThisAppsRegister(): void
    {
        $this->resolvedZaak = null;
        $this->objectService->expects($this->never())->method('getAuditTrail');

        $response = $this->controller->index('an-id-from-another-register');

        $this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
    }//end testIndexAnswers404ForAnIdOutsideThisAppsRegister()


    /**
     * The same for ::show.
     *
     * @return void
     */
    public function testShowAnswers404ForAnIdOutsideThisAppsRegister(): void
    {
        $this->resolvedZaak = null;
        $this->objectService->expects($this->never())->method('getAuditTrail');

        $response = $this->controller->show('an-id-from-another-register', 'e1');

        $this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
    }//end testShowAnswers404ForAnIdOutsideThisAppsRegister()


    public function testWriteVerbsReturn405WithAllowHeader(): void
    {
        foreach (
            [
                $this->controller->create(self::ZAAK),
                $this->controller->update(self::ZAAK, 'e1'),
                $this->controller->destroy(self::ZAAK, 'e1'),
            ] as $response
        ) {
            $this->assertSame(Http::STATUS_METHOD_NOT_ALLOWED, $response->getStatus());
        }
    }//end testWriteVerbsReturn405WithAllowHeader()


    public function testUnauthenticatedReadReturns401(): void
    {
        $session = $this->createMock(IUserSession::class);
        $session->method('getUser')->willReturn(null);
        $controller = new ZaakAuditTrailController(
            'zaakafhandelapp',
            $this->createMock(IRequest::class),
            $this->objectService,
            $this->createMock(IURLGenerator::class),
            $session
        );

        $this->assertSame(Http::STATUS_UNAUTHORIZED, $controller->index(self::ZAAK)->getStatus());
    }//end testUnauthenticatedReadReturns401()
}//end class
