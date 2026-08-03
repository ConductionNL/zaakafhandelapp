<?php

/**
 * Unit tests for ZaakBesluitenController.
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

use OCA\ZaakAfhandelApp\Controller\ZaakBesluitenController;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Locks the ZRC zaakbesluiten contract: zaak-scoped listing, ZGW
 * {url, uuid, zaak, besluit} shape, create validation (missing zaak → 404,
 * bad besluit → 400, client id stripped), cross-zaak read → 404, delete
 * removes the relation only, and the 401 unauthenticated guard.
 */
class ZaakBesluitenControllerTest extends TestCase
{

    /**
     * @var IRequest&MockObject
     */
    private $request;

    /**
     * @var ObjectService&MockObject
     */
    private $objectService;

    /**
     * @var ZaakBesluitenController
     */
    private $controller;

    private const ZAAK = 'zaak-1';


    protected function setUp(): void
    {
        $this->request       = $this->createMock(IRequest::class);
        $this->objectService = $this->createMock(ObjectService::class);

        $urlGenerator = $this->createMock(IURLGenerator::class);
        $urlGenerator->method('getAbsoluteURL')->willReturnArgument(0);

        $session = $this->createMock(IUserSession::class);
        $session->method('getUser')->willReturn($this->createMock(IUser::class));

        $this->controller = new ZaakBesluitenController(
            'zaakafhandelapp',
            $this->request,
            $this->objectService,
            $urlGenerator,
            $session
        );
    }//end setUp()


    public function testIndexReturnsOnlyTheRoutedZaaksRelationsInZgwShape(): void
    {
        $this->objectService->expects($this->once())
            ->method('getObjects')
            ->willReturnCallback(
                function (string $objectType, $limit = null, $offset = null, $filters = []) {
                    $this->assertSame('zaakbesluit', $objectType);
                    $this->assertSame(['zaak' => self::ZAAK], $filters);

                    return [['id' => 'b1', 'zaak' => self::ZAAK, 'besluit' => 'be-1']];
                }
            );

        $response = $this->controller->index(self::ZAAK);
        $data     = $response->getData();

        $this->assertSame(Http::STATUS_OK, $response->getStatus());
        $this->assertCount(1, $data['results']);
        $this->assertSame(['url', 'uuid', 'zaak', 'besluit'], array_keys($data['results'][0]));
        $this->assertSame('be-1', $data['results'][0]['besluit']);
    }//end testIndexReturnsOnlyTheRoutedZaaksRelationsInZgwShape()


    public function testShowReturns404OnCrossZaakRead(): void
    {
        $this->objectService->method('getObject')
            ->with('zaakbesluit', 'b1')
            ->willReturn(['id' => 'b1', 'zaak' => 'other-zaak', 'besluit' => 'be-1']);

        $response = $this->controller->show(self::ZAAK, 'b1');

        $this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
    }//end testShowReturns404OnCrossZaakRead()


    public function testCreateStripsClientIdValidatesAndForcesZaak(): void
    {
        $this->request->method('getParams')->willReturn(['id' => 'attacker', 'besluit' => 'be-1']);
        $this->objectService->method('getObject')->willReturnMap(
            [
                ['zaken', self::ZAAK, [], ['id' => self::ZAAK]],
                ['besluiten', 'be-1', [], ['id' => 'be-1']],
            ]
        );
        $this->objectService->expects($this->once())
            ->method('saveObject')
            ->with(
                'zaakbesluit',
                $this->callback(fn (array $d): bool => isset($d['id']) === false && $d['zaak'] === self::ZAAK)
            )
            ->willReturn(['id' => 'b9', 'zaak' => self::ZAAK, 'besluit' => 'be-1']);

        $response = $this->controller->create(self::ZAAK);

        $this->assertSame(Http::STATUS_CREATED, $response->getStatus());
    }//end testCreateStripsClientIdValidatesAndForcesZaak()


    public function testCreateRejectsUnresolvableBesluitWith400(): void
    {
        $this->request->method('getParams')->willReturn(['besluit' => 'missing']);
        $this->objectService->method('getObject')->willReturnCallback(
            function (string $type) {
                if ($type === 'zaken') {
                    return ['id' => self::ZAAK];
                }

                throw new \Exception('not found');
            }
        );
        $this->objectService->expects($this->never())->method('saveObject');

        $response = $this->controller->create(self::ZAAK);

        $this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
    }//end testCreateRejectsUnresolvableBesluitWith400()


    public function testCreateReturns404WhenZaakDoesNotExist(): void
    {
        $this->request->method('getParams')->willReturn(['besluit' => 'be-1']);
        $this->objectService->method('getObject')->willThrowException(new \Exception('not found'));
        $this->objectService->expects($this->never())->method('saveObject');

        $response = $this->controller->create(self::ZAAK);

        $this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
    }//end testCreateReturns404WhenZaakDoesNotExist()


    public function testDestroyRemovesRelationOnly(): void
    {
        $this->objectService->method('getObject')->willReturn(['id' => 'b1', 'zaak' => self::ZAAK]);
        $this->objectService->expects($this->once())->method('deleteObject')->with('zaakbesluit', 'b1')->willReturn(true);

        $response = $this->controller->destroy(self::ZAAK, 'b1');

        $this->assertSame(Http::STATUS_NO_CONTENT, $response->getStatus());
    }//end testDestroyRemovesRelationOnly()


    public function testDestroyRefusesForeignZaakRelation(): void
    {
        $this->objectService->method('getObject')->willReturn(['id' => 'b1', 'zaak' => 'other-zaak']);
        $this->objectService->expects($this->never())->method('deleteObject');

        $response = $this->controller->destroy(self::ZAAK, 'b1');

        $this->assertSame(Http::STATUS_NOT_FOUND, $response->getStatus());
    }//end testDestroyRefusesForeignZaakRelation()


    public function testUnauthenticatedIndexReturns401(): void
    {
        $session = $this->createMock(IUserSession::class);
        $session->method('getUser')->willReturn(null);
        $controller = new ZaakBesluitenController(
            'zaakafhandelapp',
            $this->request,
            $this->objectService,
            $this->createMock(IURLGenerator::class),
            $session
        );

        $this->assertSame(Http::STATUS_UNAUTHORIZED, $controller->index(self::ZAAK)->getStatus());
    }//end testUnauthenticatedIndexReturns401()
}//end class
