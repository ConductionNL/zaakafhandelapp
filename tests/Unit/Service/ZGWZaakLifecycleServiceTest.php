<?php

/**
 * Unit tests for ZGWZaakLifecycleService.
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
use OCA\OpenRegister\Service\ObjectService;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakCloseService;
use OCA\ZaakAfhandelApp\Service\ZGWZaakLifecycleService;
use PHPUnit\Framework\TestCase;

/**
 * Tests ZGWZaakLifecycleService — deleteZaak ref-skip + find(_extend:) usage.
 *
 * Locks: deleteZaak() skips non-string / empty zaakinformatieobjecten refs
 * (#278) so a TypeError never aborts the cascade; the private find() helper
 * passes the resolved id and _extend through the named-parameter ObjectService
 * API; reopen/vertrouwelijkheid inherit + enforce correctly.
 */
class ZGWZaakLifecycleServiceTest extends TestCase
{

    /**
     * @var ObjectService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectService;

    /**
     * @var ZGWZaakCloseService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $closeService;

    /**
     * @var ZGWRegistryService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var ZGWZaakLifecycleService
     */
    private $service;

    /**
     * Wire the service with mocked collaborators.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectService = $this->createMock(ObjectService::class);
        $this->closeService  = $this->createMock(ZGWZaakCloseService::class);
        $this->registry      = $this->createMock(ZGWRegistryService::class);

        $mapperService = $this->createMock(ObjectMapperService::class);
        $mapperService->method('getOpenRegisters')->willReturn($this->objectService);

        $this->service = new ZGWZaakLifecycleService($mapperService, $this->closeService, $this->registry);
    }//end setUp()

    /**
     * Build a real ObjectEntity carrying the given payload.
     *
     * @param array<string,mixed> $data The object payload.
     *
     * @return ObjectEntity
     */
    private function entity(array $data): ObjectEntity
    {
        $entity = new ObjectEntity();
        $entity->setObject($data);
        return $entity;
    }//end entity()

    /**
     * REGRESSION (#278): deleteZaak() must skip null / empty / non-string
     * zaakinformatieobjecten refs so a TypeError never escapes and aborts the
     * cascade. Only the valid string ref reaches deleteObject().
     *
     * @return void
     */
    public function testDeleteZaakSkipsInvalidZaakinformatieobjectenRefs(): void
    {
        $rendered = [
            'zaakinformatieobjecten' => [
                'http://example/zio/valid',
                null,
                '',
                ['nested' => 'array'],
            ],
        ];

        $this->objectService->method('renderEntity')->willReturn($rendered);

        // deleteObjects() handles the (here empty) singleton/rollen group.
        $this->objectService->method('deleteObjects')->willReturn([]);

        // The registry only ever resolves the single valid string ref.
        $this->registry->expects($this->once())
            ->method('getObjectIdByEndpointUrl')
            ->with('http://example/zio/valid')
            ->willReturn('uuid-valid');

        // deleteObject() is called exactly once — for the valid ref only.
        $this->objectService->expects($this->once())
            ->method('deleteObject')
            ->with('uuid-valid')
            ->willReturn(true);

        $this->service->deleteZaak($this->entity(['identificatie' => 'ZAAK-1']));
    }//end testDeleteZaakSkipsInvalidZaakinformatieobjectenRefs()

    /**
     * deleteZaak() with no zaakinformatieobjecten at all never calls
     * deleteObject() (the foreach body is skipped entirely).
     *
     * @return void
     */
    public function testDeleteZaakWithNoZaakinformatieobjectenCallsNoDeleteObject(): void
    {
        $this->objectService->method('renderEntity')->willReturn(['identificatie' => 'ZAAK-2']);
        $this->objectService->method('deleteObjects')->willReturn([]);
        $this->objectService->expects($this->never())->method('deleteObject');

        $this->service->deleteZaak($this->entity([]));
    }//end testDeleteZaakWithNoZaakinformatieobjectenCallsNoDeleteObject()

    /**
     * reopenZaak() on a non-eindstatus resolves the zaak through the
     * named-parameter find() API (id resolved via the registry, _extend: []),
     * clears the archive metadata, and saves.
     *
     * @return void
     */
    public function testReopenZaakClearsArchiveMetadataViaNamedFind(): void
    {
        $this->closeService->method('isEindStatus')->willReturn(false);

        $this->registry->method('getObjectIdByEndpointUrl')
            ->with('http://example/zaak/7')
            ->willReturn('zaak-uuid-7');

        $zaak = $this->entity(
            [
                'einddatum'         => '2024-01-01',
                'archiefactiedatum' => '2030-01-01',
                'archiefnominatie'  => 'vernietigen',
            ]
        );
        $zaak->setRegister('5');
        $zaak->setSchema('9');

        // Assert the named-parameter contract: id is the registry-resolved
        // value, _extend is the (empty) default.
        $this->objectService->expects($this->once())
            ->method('find')
            ->with('zaak-uuid-7', [])
            ->willReturn($zaak);

        $savedPayload = null;
        $this->objectService->method('saveObject')->willReturnCallback(
            function ($object) use (&$savedPayload) {
                $savedPayload = $object->jsonSerialize();
                return $object;
            }
        );

        $this->service->reopenZaak($this->entity(['zaak' => 'http://example/zaak/7']));

        $this->assertNull($savedPayload['einddatum']);
        $this->assertNull($savedPayload['archiefactiedatum']);
        $this->assertNull($savedPayload['archiefnominatie']);
    }//end testReopenZaakClearsArchiveMetadataViaNamedFind()

    /**
     * reopenZaak() on an eindstatus is a no-op: no find, no save.
     *
     * @return void
     */
    public function testReopenZaakOnEindstatusIsNoop(): void
    {
        $this->closeService->method('isEindStatus')->willReturn(true);
        $this->objectService->expects($this->never())->method('find');
        $this->objectService->expects($this->never())->method('saveObject');

        $this->service->reopenZaak($this->entity(['zaak' => 'http://example/zaak/7']));
        $this->addToAssertionCount(1);
    }//end testReopenZaakOnEindstatusIsNoop()

    /**
     * setVertrouwelijkheidaanduiding() inherits the zaaktype default when the
     * zaak has no classification yet, and persists the change.
     *
     * @return void
     */
    public function testVertrouwelijkheidInheritsZaaktypeDefault(): void
    {
        $this->registry->method('getObjectIdByEndpointUrl')->willReturn('zt-uuid');

        $zaaktype = $this->entity(['vertrouwelijkheidaanduiding' => 'zaakvertrouwelijk']);
        $this->objectService->method('find')->willReturn($zaaktype);

        $saved = null;
        $this->objectService->method('saveObject')->willReturnCallback(
            function ($object) use (&$saved) {
                $saved = $object->jsonSerialize();
                return $object;
            }
        );

        $zaak = $this->entity(['zaaktype' => 'http://example/zaaktype/1']);
        $this->service->setVertrouwelijkheidaanduiding($zaak);

        $this->assertSame('zaakvertrouwelijk', $saved['vertrouwelijkheidaanduiding']);
    }//end testVertrouwelijkheidInheritsZaaktypeDefault()

    /**
     * setVertrouwelijkheidaanduiding() with no zaaktype linked is a no-op: there
     * is no minimum classification to enforce, so nothing is resolved or saved.
     *
     * @return void
     */
    public function testVertrouwelijkheidWithoutZaaktypeIsNoop(): void
    {
        $this->objectService->expects($this->never())->method('find');
        $this->objectService->expects($this->never())->method('saveObject');

        $this->service->setVertrouwelijkheidaanduiding($this->entity(['identificatie' => 'ZAAK-9']));
        $this->addToAssertionCount(1);
    }//end testVertrouwelijkheidWithoutZaaktypeIsNoop()

    /**
     * setVertrouwelijkheidaanduiding() raises a too-low classification up to the
     * zaaktype minimum (confidentiality-lowering rule, #281).
     *
     * @return void
     */
    public function testVertrouwelijkheidRaisesTooLowClassification(): void
    {
        $this->registry->method('getObjectIdByEndpointUrl')->willReturn('zt-uuid');

        $zaaktype = $this->entity(['vertrouwelijkheidaanduiding' => 'vertrouwelijk']);
        $this->objectService->method('find')->willReturn($zaaktype);

        $saved = null;
        $this->objectService->method('saveObject')->willReturnCallback(
            function ($object) use (&$saved) {
                $saved = $object->jsonSerialize();
                return $object;
            }
        );

        $zaak = $this->entity(
            [
                'zaaktype'                    => 'http://example/zaaktype/1',
                'vertrouwelijkheidaanduiding' => 'openbaar',
            ]
        );
        $this->service->setVertrouwelijkheidaanduiding($zaak);

        $this->assertSame('vertrouwelijk', $saved['vertrouwelijkheidaanduiding']);
    }//end testVertrouwelijkheidRaisesTooLowClassification()
}//end class
