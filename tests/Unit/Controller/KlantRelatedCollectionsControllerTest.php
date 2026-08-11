<?php

/**
 * Wire-contract tests for the klant related-collection endpoints.
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

use OCA\ZaakAfhandelApp\Controller\KlantenController;
use OCA\ZaakAfhandelApp\Service\KlantContactSyncService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Locks the wire contract of the four klant sub-collection endpoints:
 *
 *   GET /api/klanten/{id}/zaken            -> klanten#getZaken
 *   GET /api/klanten/{id}/taken            -> klanten#getTaken
 *   GET /api/klanten/{id}/berichten        -> klanten#getBerichten
 *   GET /api/klanten/{id}/contactmomenten  -> klanten#getContactmomenten
 *
 * All four are #[NoAdminRequired] routes that the klant detail view calls to
 * populate its tabs. Two things about them are load-bearing and neither is
 * visible from the response body alone:
 *
 *   1. WHICH object type each one queries. They all look identical at the call
 *      site; a copy/paste slip that made getTaken() read 'zaken' would still
 *      return a well-formed `{results, total}` envelope and would still render.
 *   2. WHICH filter key scopes the query. Three of them filter on `klant`, but
 *      getBerichten() filters on `gebruikerID` — the berichten schema keys its
 *      owner differently. Dropping or renaming that filter turns a per-klant
 *      tab into "every bericht on the instance", which on a #[NoAdminRequired]
 *      route is a data leak, not a display bug.
 *
 * So the assertions below pin the (object type, filter) pair the service is
 * called with, alongside the status and the envelope the frontend reads.
 */
class KlantRelatedCollectionsControllerTest extends TestCase
{

    /**
     * The klant id used as the route parameter throughout.
     *
     * @var string
     */
    private const KLANT_ID = 'klant-42';

    /**
     * @var ObjectService&MockObject
     */
    private $objectService;


    protected function setUp(): void
    {
        $this->objectService = $this->createMock(ObjectService::class);

    }//end setUp()


    /**
     * Build a KlantenController whose session resolves to a user (or not).
     *
     * @param bool $authenticated Whether IUserSession returns a user.
     *
     * @return KlantenController The controller under test.
     */
    private function makeController(bool $authenticated=true): KlantenController
    {
        $session = $this->createMock(IUserSession::class);
        $session->method('getUser')->willReturn($authenticated === true ? $this->createMock(IUser::class) : null);

        return new KlantenController(
            'zaakafhandelapp',
            $this->createMock(IRequest::class),
            $this->objectService,
            $session,
            $this->createMock(KlantContactSyncService::class),
            $this->createMock(LoggerInterface::class)
        );

    }//end makeController()


    /**
     * Call the named sub-collection endpoint on the given controller.
     *
     * Spelled out as a match rather than `$controller->{$method}()` so each
     * endpoint has a real, greppable call site — a dynamic dispatch reads as
     * "no test" to any tool that looks for one, including hydra gate-25.
     *
     * @param KlantenController $controller The controller under test.
     * @param string            $method     The endpoint to call.
     *
     * @return JSONResponse The response returned.
     */
    private function invoke(KlantenController $controller, string $method): JSONResponse
    {
        return match ($method) {
            'getZaken' => $controller->getZaken(self::KLANT_ID),
            'getTaken' => $controller->getTaken(self::KLANT_ID),
            'getBerichten' => $controller->getBerichten(self::KLANT_ID),
            'getContactmomenten' => $controller->getContactmomenten(self::KLANT_ID),
        };

    }//end invoke()


    /**
     * The four sub-collections, with the object type and the filter key each
     * one must scope its query by.
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function collectionProvider(): array
    {
        return [
            'zaken'           => ['getZaken', 'zaken', 'klant'],
            'taken'           => ['getTaken', 'taken', 'klant'],
            // Not 'klant': berichten carry the owner on gebruikerID.
            'berichten'       => ['getBerichten', 'berichten', 'gebruikerID'],
            'contactmomenten' => ['getContactmomenten', 'contactmomenten', 'klant'],
        ];
    }//end collectionProvider()


    /**
     * Each endpoint queries its OWN object type, scoped to the klant from the
     * route, and answers 200 with the service envelope untouched.
     *
     * @param string $method     The controller method under test.
     * @param string $objectType The object type it must query.
     * @param string $filterKey  The filter key it must scope by.
     *
     * @return void
     */
    #[DataProvider('collectionProvider')]
    public function testCollectionIsScopedToTheKlantAndReturnsTheEnvelope(
        string $method,
        string $objectType,
        string $filterKey
    ): void {
        $envelope = [
            'results' => [['id' => 'a'], ['id' => 'b']],
            'total'   => 2,
        ];

        $this->objectService->expects($this->once())
            ->method('getResultArrayForRequest')
            ->with($objectType, [$filterKey => self::KLANT_ID])
            ->willReturn($envelope);

        $response = $this->invoke($this->makeController(), $method);

        $this->assertSame(Http::STATUS_OK, $response->getStatus(), $method.' status');
        $this->assertSame($envelope, $response->getData(), $method.' body');
    }//end testCollectionIsScopedToTheKlantAndReturnsTheEnvelope()


    /**
     * An unauthenticated caller gets 401 and the store is never queried — the
     * guard must sit in front of the read, not behind it.
     *
     * @param string $method The controller method under test.
     *
     * @return void
     */
    #[DataProvider('collectionProvider')]
    public function testUnauthenticatedGets401WithoutQueryingTheStore(string $method): void
    {
        $this->objectService->expects($this->never())->method('getResultArrayForRequest');

        $response = $this->invoke($this->makeController(false), $method);

        $this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus(), $method.' status');
        $this->assertArrayHasKey('error', $response->getData(), $method.' body');
    }//end testUnauthenticatedGets401WithoutQueryingTheStore()
}//end class
