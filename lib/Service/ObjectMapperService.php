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
     * REFUSES a non-numeric register/schema instead of forwarding it.
     *
     * OpenRegister's `ObjectService::getMapper()` silently discards any
     * non-numeric argument:
     *
     *     // openregister lib/Service/ObjectService.php:4654-4659
     *     if (is_string($register) === true && is_numeric($register) === false) {
     *         $register = null; $schema = null;
     *     }
     *
     * so a slug produces an UNCONSTRAINED adapter whose `find()` resolves any
     * uuid in ANY register on the instance (openregister#2434).
     *
     * The app's own tooling never writes a slug here — `src/views/settings/`
     * stores `register.id.toString()` / `schema.id.toString()`, and
     * `tests/integration/ci-seed.sh` writes `str(register['id'])` — but
     * `SettingsController::WRITABLE_KEYS` accepts any string and
     * `occ config:app:set` accepts anything at all, so a slug-configured install
     * is reachable and is silently unscoped.
     *
     * ⚠️ It is deliberately a REFUSAL and not a slug→id lookup. Resolving the
     * slug would REPAIR endpoints that are currently dead in such an install
     * (create/update raise `CascadingHandler … Argument #2 ($schema) … null
     * given`, collections answer `{"results":[],"total":0}`), and those
     * endpoints have no per-object authorisation to come back to — repairing
     * them would convert dead code into a live IDOR. Per-object authorisation is
     * still missing (zaakafhandelapp#347), so the safe direction is closed:
     * refuse, loudly, and leave the misconfiguration visible.
     *
     * @param string $objectTypeLower The lowercase object type
     *
     * @return mixed The OpenRegister mapper, bound to this app's register/schema
     * @throws Exception When OpenRegister is unavailable, the type is not
     *                   configured, or the configuration is not a numeric id.
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
            register: $this->requireNumericId($register, 'register', $objectTypeLower),
            schema: $this->requireNumericId($schema, 'schema', $objectTypeLower)
        );
    }//end getOpenRegisterMapper()

    /**
     * Require a configured register/schema identifier to be a numeric id.
     *
     * @param string $value      The configured value.
     * @param string $kind       Either 'register' or 'schema', for the message.
     * @param string $objectType The object type whose configuration this is.
     *
     * @return integer The numeric id.
     * @throws Exception When the value is not numeric, because OpenRegister
     *                   would silently drop it and return an adapter scoped to
     *                   nothing (openregister#2434).
     */
    private function requireNumericId(string $value, string $kind, string $objectType): int
    {
        if (is_numeric($value) === false) {
            throw new Exception(
                "Misconfigured $kind for '$objectType': expected a numeric OpenRegister id, got '$value'. "
                ."A non-numeric value is discarded by OpenRegister and yields a mapper scoped to no "
                ."register at all, so it is refused rather than forwarded."
            );
        }

        return (int) $value;
    }//end requireNumericId()

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
