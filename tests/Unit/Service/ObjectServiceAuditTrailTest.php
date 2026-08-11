<?php

/**
 * Unit tests for ObjectService::getAuditTrail() — the cross-object audit leak.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Service
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

namespace OCA\ZaakAfhandelApp\Tests\Unit\Service;

use OCA\OpenRegister\Service\ObjectService as OpenRegisterObjectService;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCA\ZaakAfhandelApp\Service\ObjectQueryService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * OpenRegister's `getLogs()` does not scope its result to the object you asked
 * for. `GetObject::findLogs()` filters on `$filters['object'] = $object->getId()`
 * — the NUMERIC row id — while objects live in
 * `oc_openregister_table_<register>_<schema>` shards and the audit table is
 * instance-global. So the same numeric id exists once per shard and the filter
 * selects across registers.
 *
 * Measured live on a rig before this filter existed: one uuid requested,
 * **HTTP 200 with 5 rows covering 3 objects in 3 different registers, every row
 * `object = 3`**. After it: 3 rows, one uuid.
 *
 * The doubles below return objects exposing `jsonSerialize()`, because that is
 * what `getLogs()` declares (`\OCA\OpenRegister\Db\AuditTrail[]`). A double
 * returning plain arrays would assert a contract the collaborator does not have.
 */
class ObjectServiceAuditTrailTest extends TestCase
{

    /**
     * The uuid under test.
     *
     * @var string
     */
    private const OBJECT_ID = 'f962a615-2108-4c33-aacb-bdf4fb4a7282';

    /**
     * A uuid belonging to a different object in a different register, which the
     * platform returns alongside ours because both are numeric row 3.
     *
     * @var string
     */
    private const FOREIGN_ID = '4c79294e-90d5-4a05-9845-8ebef6039b6b';

    /**
     * @var ObjectMapperService&MockObject
     */
    private $mapperService;

    /**
     * @var OpenRegisterObjectService&MockObject
     */
    private $openRegister;


    /**
     * Set up collaborators.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mapperService = $this->createMock(ObjectMapperService::class);
        $this->openRegister  = $this->createMock(OpenRegisterObjectService::class);
        $this->mapperService->method('getOpenRegisters')->willReturn($this->openRegister);
    }//end setUp()


    /**
     * Build the service under test.
     *
     * @return ObjectService
     */
    private function service(): ObjectService
    {
        return new ObjectService($this->mapperService, $this->createMock(ObjectQueryService::class));
    }//end service()


    /**
     * Build an AuditTrail-shaped double for the given object uuid.
     *
     * @param string $objectUuid The uuid the entry belongs to.
     * @param string $action     The recorded action.
     *
     * @return object An entity exposing jsonSerialize(), as getLogs() declares.
     */
    private function auditEntry(string $objectUuid, string $action): object
    {
        return new class($objectUuid, $action) {
            /**
             * @param string $objectUuid The uuid the entry belongs to.
             * @param string $action     The recorded action.
             */
            public function __construct(private string $objectUuid, private string $action)
            {
            }

            /**
             * @return array<string,mixed>
             */
            public function jsonSerialize(): array
            {
                // `object` is the NUMERIC row id, identical across shards — this
                // is precisely why the platform-side filter cannot separate them.
                return [
                    'object'     => 3,
                    'objectUuid' => $this->objectUuid,
                    'action'     => $this->action,
                ];
            }
        };
    }//end auditEntry()


    /**
     * Rows belonging to another object are dropped; the requested object's rows
     * are returned in full and in order.
     *
     * @return void
     */
    public function testEntriesForOtherObjectsAreNotReturned(): void
    {
        $this->openRegister->method('getLogs')->willReturn(
            [
                $this->auditEntry(self::FOREIGN_ID, 'create'),
                $this->auditEntry(self::OBJECT_ID, 'create'),
                $this->auditEntry(self::FOREIGN_ID, 'read'),
                $this->auditEntry(self::OBJECT_ID, 'update'),
            ]
        );

        $trail = $this->service()->getAuditTrail(self::OBJECT_ID);

        $this->assertCount(2, $trail);
        $this->assertSame([self::OBJECT_ID], array_values(array_unique(array_column($trail, 'objectUuid'))));
        $this->assertSame(['create', 'update'], array_column($trail, 'action'));
        $this->assertSame([0, 1], array_keys($trail), 'keys must be re-indexed for a JSON array');
    }//end testEntriesForOtherObjectsAreNotReturned()


    /**
     * A uuid differing only in case still belongs to this object.
     *
     * @return void
     */
    public function testUuidComparisonIsCaseInsensitive(): void
    {
        $this->openRegister->method('getLogs')->willReturn(
            [$this->auditEntry(strtoupper(self::OBJECT_ID), 'create')]
        );

        $this->assertCount(1, $this->service()->getAuditTrail(self::OBJECT_ID));
    }//end testUuidComparisonIsCaseInsensitive()


    /**
     * An entry that does not say which object it belongs to cannot be shown to
     * be this object's, so it is dropped rather than passed through.
     *
     * @return void
     */
    public function testEntriesWithoutAnObjectUuidAreDropped(): void
    {
        $this->openRegister->method('getLogs')->willReturn(
            [
                new class {
                    /**
                     * @return array<string,mixed>
                     */
                    public function jsonSerialize(): array
                    {
                        return ['object' => 3, 'action' => 'create'];
                    }
                },
            ]
        );

        $this->assertSame([], $this->service()->getAuditTrail(self::OBJECT_ID));
    }//end testEntriesWithoutAnObjectUuidAreDropped()


    /**
     * With OpenRegister absent the trail is empty rather than fatal.
     *
     * @return void
     */
    public function testMissingOpenRegisterYieldsAnEmptyTrail(): void
    {
        $mapperService = $this->createMock(ObjectMapperService::class);
        $mapperService->method('getOpenRegisters')->willReturn(null);

        $service = new ObjectService($mapperService, $this->createMock(ObjectQueryService::class));

        $this->assertSame([], $service->getAuditTrail(self::OBJECT_ID));
    }//end testMissingOpenRegisterYieldsAnEmptyTrail()
}//end class
