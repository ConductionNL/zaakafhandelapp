<?php

/**
 * Unit tests for ObjectMapperService.
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

use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\SchemaMapper;
use OCA\ZaakAfhandelApp\Service\ObjectMapperService;
use OCP\App\IAppManager;
use OCP\IAppConfig;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Tests ObjectMapperService::getRegisters() — the /settings 500 fix.
 *
 * getRegisters() resolves RegisterMapper + SchemaMapper from the container and
 * returns RegisterMapper::findAll() serialized with each register's `schemas`
 * id-list expanded into full schema objects, mirroring the OpenRegister
 * RegistersController `_extend=['schemas']` shape the settings UI consumes.
 */
class ObjectMapperServiceTest extends TestCase
{

    /**
     * @var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $container;

    /**
     * @var IAppManager&\PHPUnit\Framework\MockObject\MockObject
     */
    private $appManager;

    /**
     * @var IAppConfig&\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * Set up shared collaborators.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->container  = $this->createMock(ContainerInterface::class);
        $this->appManager = $this->createMock(IAppManager::class);
        $this->config     = $this->createMock(IAppConfig::class);
    }//end setUp()

    /**
     * Build the service under test.
     *
     * @return ObjectMapperService
     */
    private function service(): ObjectMapperService
    {
        return new ObjectMapperService($this->container, $this->appManager, $this->config);
    }//end service()

    /**
     * Build a serializable register double whose jsonSerialize returns $data.
     *
     * @param array<string,mixed> $data The serialized register payload.
     *
     * @return object
     */
    private function registerDouble(array $data): object
    {
        return new class($data) {
            /**
             * @param array<string,mixed> $data Payload.
             */
            public function __construct(private array $data)
            {
            }//end __construct()

            /**
             * @return array<string,mixed>
             */
            public function jsonSerialize(): array
            {
                return $this->data;
            }//end jsonSerialize()
        };
    }//end registerDouble()

    /**
     * Build a serializable schema double whose jsonSerialize returns $data.
     *
     * @param array<string,mixed> $data The serialized schema payload.
     *
     * @return object
     */
    private function schemaDouble(array $data): object
    {
        return new class($data) {
            /**
             * @param array<string,mixed> $data Payload.
             */
            public function __construct(private array $data)
            {
            }//end __construct()

            /**
             * @return array<string,mixed>
             */
            public function jsonSerialize(): array
            {
                return $this->data;
            }//end jsonSerialize()
        };
    }//end schemaDouble()

    /**
     * When OpenRegister is not installed, getRegisters() returns an empty array
     * (and never touches the container) instead of 500-ing.
     *
     * @return void
     */
    public function testGetRegistersReturnsEmptyWhenOpenRegisterNotInstalled(): void
    {
        $this->appManager->method('getInstalledApps')->willReturn(['files', 'zaakafhandelapp']);
        $this->container->expects($this->never())->method('get');

        $this->assertSame([], $this->service()->getRegisters());
    }//end testGetRegistersReturnsEmptyWhenOpenRegisterNotInstalled()

    /**
     * When the mappers cannot be resolved from the container, getRegisters()
     * swallows the failure and returns an empty array (no 500).
     *
     * @return void
     */
    public function testGetRegistersReturnsEmptyWhenMappersUnavailable(): void
    {
        $this->appManager->method('getInstalledApps')->willReturn(['openregister']);
        $this->container->method('get')->willThrowException(new \RuntimeException('not found'));

        $this->assertSame([], $this->service()->getRegisters());
    }//end testGetRegistersReturnsEmptyWhenMappersUnavailable()

    /**
     * Happy path: each register's `schemas` id-list is expanded into full
     * schema objects ({ id, title }) — the exact shape the settings UI reads.
     *
     * @return void
     */
    public function testGetRegistersExpandsSchemaIdsIntoObjects(): void
    {
        $this->appManager->method('getInstalledApps')->willReturn(['openregister']);

        $registerMapper = $this->createMock(RegisterMapper::class);
        $schemaMapper   = $this->createMock(SchemaMapper::class);

        $registerMapper->method('findAll')->willReturn(
            [$this->registerDouble(['id' => 1, 'title' => 'ZRC', 'schemas' => [10, 11]])]
        );

        $schemaMapper->method('find')->willReturnCallback(
            function ($id) {
                $titles = [10 => 'Zaak', 11 => 'Status'];
                return $this->schemaDouble(['id' => $id, 'title' => $titles[$id]]);
            }
        );

        $this->container->method('get')->willReturnCallback(
            function (string $id) use ($registerMapper, $schemaMapper) {
                return match ($id) {
                    'OCA\OpenRegister\Db\RegisterMapper' => $registerMapper,
                    'OCA\OpenRegister\Db\SchemaMapper'   => $schemaMapper,
                    default                              => throw new \RuntimeException('unexpected '.$id),
                };
            }
        );

        $result = $this->service()->getRegisters();

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('ZRC', $result[0]['title']);
        $this->assertSame(
            [
                ['id' => 10, 'title' => 'Zaak'],
                ['id' => 11, 'title' => 'Status'],
            ],
            $result[0]['schemas']
        );
    }//end testGetRegistersExpandsSchemaIdsIntoObjects()

    /**
     * A register whose `schemas` field is missing/non-array is normalised to an
     * empty list rather than throwing.
     *
     * @return void
     */
    public function testGetRegistersNormalisesMissingSchemasToEmptyList(): void
    {
        $this->appManager->method('getInstalledApps')->willReturn(['openregister']);

        $registerMapper = $this->createMock(RegisterMapper::class);
        $schemaMapper   = $this->createMock(SchemaMapper::class);

        $registerMapper->method('findAll')->willReturn(
            [$this->registerDouble(['id' => 2, 'title' => 'No schemas'])]
        );
        $schemaMapper->expects($this->never())->method('find');

        $this->container->method('get')->willReturnCallback(
            fn (string $id) => $id === 'OCA\OpenRegister\Db\RegisterMapper' ? $registerMapper : $schemaMapper
        );

        $result = $this->service()->getRegisters();

        $this->assertCount(1, $result);
        $this->assertSame([], $result[0]['schemas']);
    }//end testGetRegistersNormalisesMissingSchemasToEmptyList()

    /**
     * A schema id that the SchemaMapper cannot resolve is skipped (the register
     * still renders) rather than aborting the whole listing.
     *
     * @return void
     */
    public function testGetRegistersSkipsUnresolvableSchemaIds(): void
    {
        $this->appManager->method('getInstalledApps')->willReturn(['openregister']);

        $registerMapper = $this->createMock(RegisterMapper::class);
        $schemaMapper   = $this->createMock(SchemaMapper::class);

        $registerMapper->method('findAll')->willReturn(
            [$this->registerDouble(['id' => 3, 'schemas' => [10, 99]])]
        );

        $schemaMapper->method('find')->willReturnCallback(
            function ($id) {
                if ($id === 99) {
                    throw new \RuntimeException('schema not found');
                }

                return $this->schemaDouble(['id' => $id, 'title' => 'Zaak']);
            }
        );

        $this->container->method('get')->willReturnCallback(
            fn (string $id) => $id === 'OCA\OpenRegister\Db\RegisterMapper' ? $registerMapper : $schemaMapper
        );

        $result = $this->service()->getRegisters();

        $this->assertCount(1, $result[0]['schemas']);
        $this->assertSame(10, $result[0]['schemas'][0]['id']);
    }//end testGetRegistersSkipsUnresolvableSchemaIds()

    /**
     * getOpenRegisters() returns null when OpenRegister is not installed, which
     * is the guard the ZGW services convert into a RuntimeException.
     *
     * @return void
     */
    public function testGetOpenRegistersReturnsNullWhenNotInstalled(): void
    {
        $this->appManager->method('getInstalledApps')->willReturn(['files']);

        $this->assertNull($this->service()->getOpenRegisters());
    }//end testGetOpenRegistersReturnsNullWhenNotInstalled()


    /**
     * Wire the container so `getMapper('zaken')` can run: OpenRegister installed,
     * the app configured for the openregister source, and the three collaborators
     * resolvable.
     *
     * @param object $openRegister   The OR ObjectService double.
     * @param object $registerMapper The RegisterMapper double.
     * @param object $schemaMapper   The SchemaMapper double.
     * @param string $registerValue  The configured `zaken_register` value.
     * @param string $schemaValue    The configured `zaken_schema` value.
     *
     * @return void
     */
    private function wireOpenRegisterSource(
        object $openRegister,
        object $registerMapper,
        object $schemaMapper,
        string $registerValue='zaakafhandelapp',
        string $schemaValue='zaak'
    ): void {
        $this->appManager->method('getInstalledApps')->willReturn(['openregister', 'zaakafhandelapp']);
        $this->config->method('getValueString')->willReturnCallback(
            static function (string $app, string $key, string $default='') use ($registerValue, $schemaValue): string {
                return match ($key) {
                    'zaken_source' => 'openregister',
                    'zaken_register' => $registerValue,
                    'zaken_schema' => $schemaValue,
                    default => $default,
                };
            }
        );
        $this->container->method('get')->willReturnCallback(
            static function (string $id) use ($openRegister, $registerMapper, $schemaMapper): object {
                return match ($id) {
                    'OCA\OpenRegister\Service\ObjectService' => $openRegister,
                    'OCA\OpenRegister\Db\RegisterMapper' => $registerMapper,
                    'OCA\OpenRegister\Db\SchemaMapper' => $schemaMapper,
                    default => throw new \RuntimeException('unexpected container id: '.$id),
                };
            }
        );
    }//end wireOpenRegisterSource()


    /**
     * Build a mapper double whose find() returns an entity with the given id.
     *
     * @param class-string $class The mapper class to double.
     * @param int          $id    The numeric id its find() resolves to.
     *
     * @return object
     */
    private function idResolvingMapper(string $class, int $id): object
    {
        $entity = new class($id) {
            /**
             * @param int $id The numeric id.
             */
            public function __construct(private int $id)
            {
            }//end __construct()

            /**
             * @return int
             */
            public function getId(): int
            {
                return $this->id;
            }//end getId()
        };

        $mapper = $this->createMock($class);
        $mapper->method('find')->willReturn($entity);

        return $mapper;
    }//end idResolvingMapper()


    /**
     * The configured register/schema SLUGS are resolved to numeric ids before
     * they reach OpenRegister's getMapper().
     *
     * This is the whole fix. `ObjectService::getMapper()` discards any
     * non-numeric argument —
     *
     *     if (is_string($register) === true && is_numeric($register) === false) {
     *         $register = null; $schema = null;
     *     }
     *
     * — and returns an UNCONSTRAINED adapter, so passing the slugs through made
     * `find()` resolve any uuid in any register on the instance. Measured live:
     * `GET api/objects/zaken/{uuid-of-a-vocabulary-object}` returned HTTP 200
     * with that other register's object; after this change, 404.
     *
     * Asserting on the ARGUMENTS rather than on the returned adapter is
     * deliberate: the adapter looks identical either way, and it is the argument
     * type that decides whether it is scoped.
     *
     * @return void
     */
    public function testConfiguredSlugsAreResolvedToNumericIdsBeforeReachingOpenRegister(): void
    {
        $openRegister = $this->createMock(\OCA\OpenRegister\Service\ObjectService::class);
        $openRegister->expects($this->once())
            ->method('getMapper')
            ->with(14, 17)
            ->willReturn(new \stdClass());

        $this->wireOpenRegisterSource(
            $openRegister,
            $this->idResolvingMapper(RegisterMapper::class, 14),
            $this->idResolvingMapper(SchemaMapper::class, 17)
        );

        $this->service()->getMapper('zaken');
    }//end testConfiguredSlugsAreResolvedToNumericIdsBeforeReachingOpenRegister()


    /**
     * A numerically-configured register/schema is passed through as an int and
     * no mapper lookup is performed.
     *
     * @return void
     */
    public function testNumericConfigurationIsPassedThroughWithoutALookup(): void
    {
        $openRegister = $this->createMock(\OCA\OpenRegister\Service\ObjectService::class);
        $openRegister->expects($this->once())->method('getMapper')->with(9, 4)->willReturn(new \stdClass());

        $registerMapper = $this->createMock(RegisterMapper::class);
        $registerMapper->expects($this->never())->method('find');
        $schemaMapper = $this->createMock(SchemaMapper::class);
        $schemaMapper->expects($this->never())->method('find');

        $this->wireOpenRegisterSource($openRegister, $registerMapper, $schemaMapper, '9', '4');

        $this->service()->getMapper('zaken');
    }//end testNumericConfigurationIsPassedThroughWithoutALookup()


    /**
     * An unresolvable slug is a hard error — it must NOT fall back to passing the
     * slug on, because that is exactly what produced the unconstrained adapter.
     *
     * @return void
     */
    public function testAnUnresolvableRegisterSlugThrowsInsteadOfFallingBack(): void
    {
        $openRegister = $this->createMock(\OCA\OpenRegister\Service\ObjectService::class);
        $openRegister->expects($this->never())->method('getMapper');

        $registerMapper = $this->createMock(RegisterMapper::class);
        $registerMapper->method('find')->willThrowException(new \RuntimeException('no such register'));

        $this->wireOpenRegisterSource(
            $openRegister,
            $registerMapper,
            $this->idResolvingMapper(SchemaMapper::class, 17)
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Could not resolve register/');

        $this->service()->getMapper('zaken');
    }//end testAnUnresolvableRegisterSlugThrowsInsteadOfFallingBack()


    /**
     * The same for an unresolvable schema slug.
     *
     * @return void
     */
    public function testAnUnresolvableSchemaSlugThrowsInsteadOfFallingBack(): void
    {
        $openRegister = $this->createMock(\OCA\OpenRegister\Service\ObjectService::class);
        $openRegister->expects($this->never())->method('getMapper');

        $schemaMapper = $this->createMock(SchemaMapper::class);
        $schemaMapper->method('find')->willThrowException(new \RuntimeException('no such schema'));

        $this->wireOpenRegisterSource(
            $openRegister,
            $this->idResolvingMapper(RegisterMapper::class, 14),
            $schemaMapper
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Could not resolve schema/');

        $this->service()->getMapper('zaken');
    }//end testAnUnresolvableSchemaSlugThrowsInsteadOfFallingBack()
}//end class
