<?php

namespace OCA\ZaakAfhandelApp\Service;

use Exception;
use InvalidArgumentException;
use OCP\App\IAppManager;
use Psr\Container\ContainerInterface;
use OCP\IAppConfig;

/**
 * Service for resolving object type mappers from configuration.
 *
 * Handles the mapping between object types and their data sources
 * (internal mappers or OpenRegister).
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ObjectMapperService
{

    /**
     * @var string $appName The name of the app
     */
    private string $appName;

    /**
     * Request-scoped slug -> numeric id cache for registers.
     *
     * @var array<string,int>
     */
    private array $registerIdCache = [];

    /**
     * Request-scoped slug -> numeric id cache for schemas.
     *
     * @var array<string,int>
     */
    private array $schemaIdCache = [];

    /**
     * Constructor for ObjectMapperService.
     *
     * @param ContainerInterface $container  The DI container
     * @param IAppManager        $appManager The app manager
     * @param IAppConfig         $config     The app configuration
     */
    public function __construct(
        private ContainerInterface $container,
        private readonly IAppManager $appManager,
        private readonly IAppConfig $config,
    ) {
        $this->appName = 'zaakafhandelapp';
    }//end __construct()

    /**
     * Gets the appropriate mapper based on the object type.
     *
     * @param string $objectType The type of object
     *
     * @return mixed The appropriate mapper
     * @throws InvalidArgumentException|Exception
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-001
     */
    public function getMapper(string $objectType): mixed
    {
        $objectTypeLower = strtolower($objectType);
        $source          = $this->config->getValueString($this->appName, $objectTypeLower.'_source', 'internal');

        if ($source === 'openregister') {
            return $this->getOpenRegisterMapper($objectTypeLower);
        }

        return match ($objectType) {
            default => throw new InvalidArgumentException("Unknown object type: $objectType"),
        };
    }//end getMapper()

    /**
     * Get an OpenRegister mapper for the given object type.
     *
     * The configured register/schema are SLUGS (`zaakafhandelapp` / `zaak`), and
     * OpenRegister's `ObjectService::getMapper()` discards any non-numeric
     * argument:
     *
     *     // openregister lib/Service/ObjectService.php:4654-4659
     *     if (is_string($register) === true && is_numeric($register) === false) {
     *         $register = null; $schema = null;
     *     }
     *
     * Passing the slugs straight through therefore returned an UNCONSTRAINED
     * adapter, and `find()` resolved any uuid in ANY register on the instance —
     * measured live: `GET api/objects/zaken/{uuid-of-a-vocabulary-object}`
     * returned HTTP 200 with that other register's object. The same nulling is
     * why every collection endpoint answered `{"results":[],"total":0}`
     * (`[MagicMapper] findAll() called without register/schema context`) and why
     * create/update raised `CascadingHandler … Argument #2 ($schema) … null given`.
     *
     * So resolve the slugs to their numeric ids here and pass those. An
     * unresolvable slug is a hard error: falling back to the slug would silently
     * restore the unconstrained adapter, which is the defect itself.
     *
     * @param string $objectTypeLower The lowercase object type
     *
     * @return mixed The OpenRegister mapper, bound to this app's register/schema
     * @throws Exception When OpenRegister is unavailable, the type is not
     *                   configured, or a configured slug does not resolve.
     */
    private function getOpenRegisterMapper(string $objectTypeLower): mixed
    {
        $openRegister = $this->getOpenRegisters();
        if ($openRegister === null) {
            throw new Exception("OpenRegister service not available");
        }

        $register = $this->config->getValueString($this->appName, $objectTypeLower.'_register', '');
        if (empty($register)) {
            throw new Exception("Register not configured for $objectTypeLower");
        }

        $schema = $this->config->getValueString($this->appName, $objectTypeLower.'_schema', '');
        if (empty($schema)) {
            throw new Exception("Schema not configured for $objectTypeLower");
        }

        return $openRegister->getMapper(
            register: $this->resolveRegisterId($register),
            schema: $this->resolveSchemaId($schema)
        );
    }//end getOpenRegisterMapper()

    /**
     * Resolve a configured register slug (or uuid) to its numeric id.
     *
     * @param string $register The configured register identifier.
     *
     * @return integer The numeric register id.
     * @throws Exception When the register cannot be resolved.
     */
    private function resolveRegisterId(string $register): int
    {
        if (is_numeric($register) === true) {
            return (int) $register;
        }

        if (isset($this->registerIdCache[$register]) === true) {
            return $this->registerIdCache[$register];
        }

        try {
            $registerMapper = $this->container->get('OCA\OpenRegister\Db\RegisterMapper');
            $id = (int) $registerMapper->find($register)->getId();
        } catch (\Throwable $e) {
            throw new Exception("Could not resolve register '$register': ".$e->getMessage());
        }

        if ($id === 0) {
            throw new Exception("Could not resolve register '$register'");
        }

        $this->registerIdCache[$register] = $id;

        return $id;
    }//end resolveRegisterId()

    /**
     * Resolve a configured schema slug (or uuid) to its numeric id.
     *
     * @param string $schema The configured schema identifier.
     *
     * @return integer The numeric schema id.
     * @throws Exception When the schema cannot be resolved.
     */
    private function resolveSchemaId(string $schema): int
    {
        if (is_numeric($schema) === true) {
            return (int) $schema;
        }

        if (isset($this->schemaIdCache[$schema]) === true) {
            return $this->schemaIdCache[$schema];
        }

        try {
            $schemaMapper = $this->container->get('OCA\OpenRegister\Db\SchemaMapper');
            $id           = (int) $schemaMapper->find($schema)->getId();
        } catch (\Throwable $e) {
            throw new Exception("Could not resolve schema '$schema': ".$e->getMessage());
        }

        if ($id === 0) {
            throw new Exception("Could not resolve schema '$schema'");
        }

        $this->schemaIdCache[$schema] = $id;

        return $id;
    }//end resolveSchemaId()

    /**
     * Attempts to retrieve the OpenRegister service.
     *
     * @return \OCA\OpenRegister\Service\ObjectService|null The service or null
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-001
     */
    public function getOpenRegisters(): ?\OCA\OpenRegister\Service\ObjectService
    {
        if (in_array(needle: 'openregister', haystack: $this->appManager->getInstalledApps())) {
            try {
                return $this->container->get('OCA\OpenRegister\Service\ObjectService');
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }//end getOpenRegisters()

    /**
     * Retrieve all OpenRegister registers with their schemas expanded.
     *
     * The OpenRegister ObjectService does not expose a register listing API;
     * registers are owned by the RegisterMapper. This method resolves that
     * mapper (and the SchemaMapper) from the container and returns each
     * register as an array with its `schemas` field expanded from a list of
     * schema IDs to a list of `{id, title, ...}` objects, mirroring the
     * OpenRegister RegistersController `_extend=['schemas']` behaviour that the
     * settings UI consumes.
     *
     * @return array<int, array<string, mixed>> Registers with expanded schemas, or an empty array when OpenRegister is unavailable.
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-001
     */
    public function getRegisters(): array
    {
        if (in_array(needle: 'openregister', haystack: $this->appManager->getInstalledApps()) === false) {
            return [];
        }

        try {
            $registerMapper = $this->container->get('OCA\OpenRegister\Db\RegisterMapper');
            $schemaMapper   = $this->container->get('OCA\OpenRegister\Db\SchemaMapper');
        } catch (Exception $e) {
            return [];
        }

        $registers    = $registerMapper->findAll(_multitenancy: false);
        $registersArr = array_map(
            static fn ($register) => $register->jsonSerialize(),
            $registers
        );

        // Expand each register's schema IDs into full schema objects so the
        // settings UI can render schema options ({ id, title }) per register.
        return array_map(
            fn (array $register): array => $this->expandRegisterSchemas(register: $register, schemaMapper: $schemaMapper),
            $registersArr
        );
    }//end getRegisters()

    /**
     * Expand a register's `schemas` field from a list of IDs to full schema objects.
     *
     * @param array<string, mixed> $register     The serialized register (its `schemas` is a list of IDs).
     * @param object               $schemaMapper The OpenRegister SchemaMapper used to resolve IDs.
     *
     * @return array<string, mixed> The register with `schemas` replaced by serialized schema objects.
     */
    private function expandRegisterSchemas(array $register, object $schemaMapper): array
    {
        if (isset($register['schemas']) === false || is_array($register['schemas']) === false) {
            $register['schemas'] = [];
            return $register;
        }

        $expandedSchemas = [];
        foreach ($register['schemas'] as $schemaId) {
            if (is_int($schemaId) === false && is_string($schemaId) === false) {
                continue;
            }

            try {
                $expandedSchemas[] = $schemaMapper->find(id: $schemaId, _multitenancy: false)->jsonSerialize();
            } catch (Exception $e) {
                // Schema not found or not accessible; skip it.
                continue;
            }
        }

        $register['schemas'] = $expandedSchemas;

        return $register;
    }//end expandRegisterSchemas()
}//end class
