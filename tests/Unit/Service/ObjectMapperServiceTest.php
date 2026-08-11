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
     * the app configured for the openregister source, and the OR service
     * resolvable.
     *
     * @param object $openRegister  The OR ObjectService double.
     * @param string $registerValue The configured `zaken_register` value.
     * @param string $schemaValue   The configured `zaken_schema` value.
     *
     * @return void
     */
    private function wireOpenRegisterSource(
        object $openRegister,
        string $registerValue='14',
        string $schemaValue='17'
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
            static function (string $id) use ($openRegister): object {
                return match ($id) {
                    'OCA\OpenRegister\Service\ObjectService' => $openRegister,
                    default => throw new \RuntimeException('unexpected container id: '.$id),
                };
            }
        );
    }//end wireOpenRegisterSource()


    /**
     * The configured numeric ids reach OpenRegister as INTEGERS.
     *
     * `ObjectService::getMapper()` keeps a numeric argument and drops any other,
     * so the argument TYPE is what decides whether the returned adapter is scoped
     * to this app's register or to nothing at all. The adapter looks identical
     * either way, which is why this asserts on the arguments rather than on the
     * return value.
     *
     * @return void
     */
    public function testNumericConfigurationReachesOpenRegisterAsIntegers(): void
    {
        $openRegister = $this->createMock(\OCA\OpenRegister\Service\ObjectService::class);
        $openRegister->expects($this->once())->method('getMapper')->with(14, 17)->willReturn(new \stdClass());

        $this->wireOpenRegisterSource($openRegister, '14', '17');

        $this->service()->getMapper('zaken');
    }//end testNumericConfigurationReachesOpenRegisterAsIntegers()


    /**
     * A non-numeric register is REFUSED and never reaches OpenRegister.
     *
     * OpenRegister replaces a non-numeric register/schema with `null` and returns
     * an adapter scoped to nothing, whose `find()` resolves any uuid in any
     * register on the instance (openregister#2434). Verified on a live instance:
     * configured with slugs, `GET api/objects/zaken/{uuid-of-an-object-in-another-register}`
     * answered HTTP 200 with that other register's object.
     *
     * ⚠️ Refusal, deliberately, rather than a slug -> id lookup. Resolving the
     * slug would REPAIR endpoints that are dead in a slug-configured install
     * (create/update raise a CascadingHandler TypeError; collections return
     * `total: 0`), and those endpoints have no per-object authorisation to fall
     * back on — repairing them would convert dead code into a live IDOR
     * (zaakafhandelapp#347). Fail closed instead.
     *
     * @return void
     */
    public function testANonNumericRegisterIsRefusedAndNeverReachesOpenRegister(): void
    {
        $openRegister = $this->createMock(\OCA\OpenRegister\Service\ObjectService::class);
        $openRegister->expects($this->never())->method('getMapper');

        $this->wireOpenRegisterSource($openRegister, 'zaakafhandelapp', '17');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Misconfigured register for .zaken./');

        $this->service()->getMapper('zaken');
    }//end testANonNumericRegisterIsRefusedAndNeverReachesOpenRegister()


    /**
     * The same for a non-numeric schema.
     *
     * @return void
     */
    public function testANonNumericSchemaIsRefusedAndNeverReachesOpenRegister(): void
    {
        $openRegister = $this->createMock(\OCA\OpenRegister\Service\ObjectService::class);
        $openRegister->expects($this->never())->method('getMapper');

        $this->wireOpenRegisterSource($openRegister, '14', 'zaak');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Misconfigured schema for .zaken./');

        $this->service()->getMapper('zaken');
    }//end testANonNumericSchemaIsRefusedAndNeverReachesOpenRegister()
}//end class
