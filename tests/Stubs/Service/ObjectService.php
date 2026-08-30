<?php

/**
 * Test stub for OCA\OpenRegister\Service\ObjectService.
 *
 * Fallback used only when the real OpenRegister app is NOT loaded (standalone /
 * CI without the app installed). Provides the named-parameter method signatures
 * the ZaakAfhandelApp ZGW services call, so PHPUnit can mock the service. When
 * the real OpenRegister runtime is present this file is never autoloaded (the
 * real class is already declared).
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\OpenRegister\Service;

use OCA\OpenRegister\Db\ObjectEntity;

/**
 * Stub for ObjectService with the named-parameter signatures used by tests.
 */
abstract class ObjectService {
	/**
	 * Find a single object by id, optionally extending related properties.
	 *
	 * @param integer|string $id Object UUID or integer id.
	 * @param array|null $_extend Properties to extend.
	 * @param boolean $files Whether to include file info.
	 * @param mixed $register Register scope.
	 * @param mixed $schema Schema scope.
	 *
	 * @return ObjectEntity|null
	 */
	abstract public function find(int|string $id, ?array $_extend = [], bool $files = false, $register = null, $schema = null): ?ObjectEntity;

	/**
	 * Find all objects matching the given configuration.
	 *
	 * @param array<string,mixed> $config Query configuration.
	 *
	 * @return array<int,ObjectEntity>
	 */
	abstract public function findAll(array $config = []): array;

	/**
	 * Persist an object.
	 *
	 * @param array|ObjectEntity $object The object to save.
	 * @param array|null $extend Extend options.
	 * @param mixed $register Register scope.
	 * @param mixed $schema Schema scope.
	 *
	 * @return ObjectEntity
	 */
	abstract public function saveObject($object, ?array $extend = [], $register = null, $schema = null): ObjectEntity;

	/**
	 * Delete a single object by uuid.
	 *
	 * @param string $uuid The object uuid.
	 *
	 * @return boolean
	 */
	abstract public function deleteObject(string $uuid): bool;

	/**
	 * Delete several objects by uuid.
	 *
	 * @param array<int,string> $uuids The object uuids.
	 *
	 * @return array<int,mixed>
	 */
	abstract public function deleteObjects(array $uuids = []): array;

	/**
	 * Render an entity to its array representation.
	 *
	 * @param ObjectEntity $entity The entity to render.
	 * @param array|null $_extend Properties to extend.
	 *
	 * @return array<string,mixed>
	 */
	abstract public function renderEntity(ObjectEntity $entity, ?array $_extend = []): array;

	/**
	 * Reset any leftover current register/schema state.
	 *
	 * @return void
	 */
	abstract public function clearCurrents(): void;

	/**
	 * Resolve a mapper for the given register/schema.
	 *
	 * @param mixed $register Register scope.
	 * @param mixed $schema Schema scope.
	 *
	 * @return mixed
	 */
	abstract public function getMapper($register = null, $schema = null);

	/**
	 * Get facets for objects matching a query.
	 *
	 * This is OpenRegister's real faceting entry point. It is declared here
	 * deliberately: ObjectQueryService::getFacets() used to call a
	 * `getAggregations()` method that exists nowhere in OpenRegister, and this
	 * stub declared neither name — so nothing in the suite could tell the two
	 * apart and the bad call reached production. A stub that omits the method
	 * its caller depends on cannot catch a wrong method name; mirroring the
	 * real signature here is what makes ObjectQueryServiceTest meaningful.
	 *
	 * @param array $query Filters plus optional `_facets` configuration.
	 *
	 * @return array
	 */
	abstract public function getFacetsForObjects(array $query = []): array;

	/**
	 * Read the audit trail for an object uuid.
	 *
	 * Signature mirrors OpenRegister's real
	 * `ObjectService::getLogs(string $uuid, array $filters = [], bool $_rbac = true, bool $_multitenancy = true): array`,
	 * whose declared return type is `\OCA\OpenRegister\Db\AuditTrail[]` — i.e.
	 * ENTITIES, not arrays. Doubles for this method must therefore return
	 * objects exposing `jsonSerialize()`; a double returning plain arrays would
	 * be a contract `getLogs()` does not have (the exact shape that kept
	 * openregister's MapsOverviewServiceTest green for its whole life).
	 *
	 * ⚠️ It does NOT scope its result to the object asked for: it filters on the
	 * NUMERIC row id, which is unique only within a register/schema shard, so it
	 * returns other objects' rows. That is why ObjectService::getAuditTrail()
	 * filters afterwards.
	 *
	 * @param string $uuid The object uuid.
	 * @param array $filters Optional filters.
	 * @param bool $_rbac Whether to apply RBAC checks.
	 * @param bool $_multitenancy Whether to apply multitenancy filtering.
	 *
	 * @return array
	 */
	abstract public function getLogs(string $uuid, array $filters = [], bool $_rbac = true, bool $_multitenancy = true): array;
}//end class
