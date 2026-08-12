<?php

namespace OCA\ZaakAfhandelApp\Service;

/**
 * Facade service for object operations used by controllers.
 *
 * Delegates mapper resolution to ObjectMapperService and
 * query operations to ObjectQueryService.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ObjectService implements IObjectService {
	/**
	 * Constructor for ObjectService.
	 *
	 * @param ObjectMapperService $mapperService The mapper resolution service
	 * @param ObjectQueryService $queryService The query operations service
	 */
	public function __construct(
		private readonly ObjectMapperService $mapperService,
		private readonly ObjectQueryService $queryService,
	) {
	}//end __construct()

	/**
	 * Gets the appropriate mapper based on the object type.
	 *
	 * @param string $objectType The type of object
	 *
	 * @return mixed The appropriate mapper
	 */
	public function getMapper(string $objectType): mixed {
		return $this->mapperService->getMapper($objectType);
	}//end getMapper()

	/**
	 * Attempts to retrieve the OpenRegister service.
	 *
	 * @return \OCA\OpenRegister\Service\ObjectService|null The service or null
	 */
	public function getOpenRegisters(): ?\OCA\OpenRegister\Service\ObjectService {
		return $this->mapperService->getOpenRegisters();
	}//end getOpenRegisters()

	/**
	 * Gets an object based on the object type and id.
	 *
	 * @param string $objectType The type of object
	 * @param string $id The id
	 * @param array $extend Extensions to apply
	 *
	 * @return mixed The retrieved object as array
	 */
	public function getObject(string $objectType, string $id, array $extend = []): mixed {
		return $this->queryService->getObject($objectType, $id, $extend);
	}//end getObject()

	/**
	 * Gets objects based on the object type and parameters.
	 *
	 * @param string $objectType The type
	 * @param integer|null $limit Max objects
	 * @param integer|null $offset Offset
	 * @param array|null $filters Filters
	 * @param array|null $sort Sort params
	 * @param string|null $search Search string
	 * @param array|null $extend Extensions
	 *
	 * @return array The retrieved objects as arrays
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getObjects(
		string $objectType,
		?int $limit = null,
		?int $offset = null,
		?array $filters = [],
		?array $sort = [],
		?string $search = null,
		?array $extend = [],
	): array {
		return $this->queryService->getObjects($objectType, $limit, $offset, $filters, $sort, $search, $extend);
	}//end getObjects()

	/**
	 * Gets facets for a specific object type.
	 *
	 * @param string $objectType The type of object
	 * @param array $filters Filters to narrow the facetted set
	 *
	 * @return array The facets, keyed by field; empty when the mapper is not OpenRegister
	 */
	public function getFacets(string $objectType, array $filters = []): array {
		return $this->queryService->getFacets($objectType, $filters);
	}//end getFacets()

	/**
	 * Gets all objects of a specific type.
	 *
	 * @param string $objectType The type of object
	 * @param integer|null $limit Maximum number of objects to return, or null for no limit
	 * @param integer|null $offset Number of objects to skip, or null for none
	 *
	 * @return array The retrieved objects
	 */
	public function getAllObjects(string $objectType, ?int $limit = null, ?int $offset = null): array {
		return $this->queryService->getAllObjects($objectType, $limit, $offset);
	}//end getAllObjects()

	/**
	 * Creates or updates an object.
	 *
	 * @param string $objectType The type of object
	 * @param array $object The object data; an `id` key makes this an update
	 *
	 * @return mixed The saved object as returned by the mapper
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function saveObject(string $objectType, array $object): mixed {
		return $this->queryService->saveObject($objectType, $object);
	}//end saveObject()

	/**
	 * Deletes an object.
	 *
	 * @param string $objectType The type of object
	 * @param string|integer $id The object id, or a URL ending in the id
	 *
	 * @return boolean True when the object was deleted, false on any failure
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function deleteObject(string $objectType, string|int $id): bool {
		return $this->queryService->deleteObject($objectType, $id);
	}//end deleteObject()

	/**
	 * Get the count of objects for a given type.
	 *
	 * @param string $objectType The type of object
	 * @param array $filters Filters to narrow the counted set
	 *
	 * @return integer The number of matching objects; 0 when the mapper is not OpenRegister
	 */
	public function getCount(string $objectType, array $filters = []): int {
		return $this->queryService->getCount($objectType, $filters);
	}//end getCount()

	/**
	 * Get a result array for a request.
	 *
	 * @param string $objectType The type of object
	 * @param array $requestParams The raw request parameters to parse into a query
	 *
	 * @return array The response body with keys: results, facets, total
	 */
	public function getResultArrayForRequest(string $objectType, array $requestParams): array {
		return $this->queryService->getResultArrayForRequest($objectType, $requestParams);
	}//end getResultArrayForRequest()

	/**
	 * Gets multiple objects by ids.
	 *
	 * @param string $objectType The type of object
	 * @param array $ids The ids to fetch; each may be an id, a URL, an entity or an array with an `id` key
	 *
	 * @return array The retrieved objects
	 */
	public function getMultipleObjects(string $objectType, array $ids): array {
		return $this->queryService->getMultipleObjects($objectType, $ids);
	}//end getMultipleObjects()

	/**
	 * Get relations for an object (objects that this object references — incoming).
	 *
	 * Routes through OR's ObjectService::getObjectUsedBy to avoid calling non-existent
	 * methods on the OR ObjectServiceMapperAdapter (C5 fix).
	 *
	 * OR resolves the relation graph from the object UUID alone, so this method takes
	 * no $objectType. It used to accept one and silently ignore it, which read as if
	 * the lookup were type-scoped when it never was; scoping by type is the caller's
	 * job (see ObjectsController::validateObjectType).
	 *
	 * @param string $id The object uuid.
	 *
	 * @return array<int, mixed> The objects referencing this one.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getRelations(string $id): array {
		$orService = $this->mapperService->getOpenRegisters();
		if ($orService === null) {
			return [];
		}

		return $orService->getObjectUsedBy($id);
	}//end getRelations()

	/**
	 * Get uses for an object (objects that this object points to — outgoing).
	 *
	 * Routes through OR's ObjectService::getObjectUses to avoid calling non-existent
	 * methods on the OR ObjectServiceMapperAdapter (C5 fix).
	 *
	 * Takes no $objectType for the same reason as getRelations(): OR resolves uses
	 * from the uuid alone, so an ignored type parameter only misrepresented the scope.
	 *
	 * @param string $id The object uuid.
	 *
	 * @return array<int, mixed> The objects this one points to.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getUses(string $id): array {
		$orService = $this->mapperService->getOpenRegisters();
		if ($orService === null) {
			return [];
		}

		return $orService->getObjectUses($id);
	}//end getUses()

	/**
	 * Get audit trail for an object.
	 *
	 * Routes through OR's ObjectService::getLogs to avoid calling the non-existent
	 * getAuditTrail method on the OR ObjectServiceMapperAdapter (C5 fix).
	 *
	 * ⚠️ `getLogs()` DOES NOT scope its result to the object you asked for.
	 * `GetObject::findLogs()` filters on `$filters['object'] = $object->getId()` —
	 * the NUMERIC row id. Objects live in `oc_openregister_table_<register>_<schema>`
	 * shards, so that id is unique only within a shard, while
	 * `oc_openregister_audit_trail` is instance-global. Measured live on a rig:
	 * one uuid asked for, **5 rows returned covering 3 objects in 3 different
	 * registers, every row `object = 3`** — i.e. another app's audit trail.
	 *
	 * So filter the returned rows down to the uuid that was actually requested.
	 * This is a defence-in-depth filter in front of an OpenRegister defect, not a
	 * fix for it; the platform-side fix is to filter on the object uuid.
	 *
	 * This method performs NO authorisation. Callers must decide for themselves
	 * whether the caller may see this object's history — see the accurate note on
	 * ObjectsController::getAuditTrail. An earlier version of this docblock
	 * claimed the callers "resolve the object through OR's RBAC"; they do not,
	 * and that sentence is exactly the kind of false assurance a reviewer marks
	 * as done (ConductionNL/zaakafhandelapp#347).
	 *
	 * @param string $id The object uuid.
	 *
	 * @return array<int, mixed> The serialized audit trail entries for THIS object.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getAuditTrail(string $id): array {
		$orService = $this->mapperService->getOpenRegisters();
		if ($orService === null) {
			return [];
		}

		$logs = $orService->getLogs($id);

		// The getLogs call returns AuditTrail entities; serialize them for JSON responses.
		$serialized = array_map(
			function ($log) {
				if (is_object($log) === true && method_exists($log, 'jsonSerialize') === true) {
					return $log->jsonSerialize();
				}

				return (array)$log;
			},
			$logs
		);

		return array_values(
			array_filter(
				$serialized,
				static function ($entry) use ($id): bool {
					if (is_array($entry) === false) {
						return false;
					}

					$uuid = $entry['objectUuid'] ?? null;

					// An entry that does not say which object it belongs to
					// cannot be shown to be this object's, so it is dropped.
					if (is_string($uuid) === false) {
						return false;
					}

					return strcasecmp($uuid, $id) === 0;
				}
			)
		);
	}//end getAuditTrail()
}//end class
