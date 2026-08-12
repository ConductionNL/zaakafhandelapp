<?php

namespace OCA\ZaakAfhandelApp\Service;

use Exception;
use InvalidArgumentException;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * Service for querying objects, building result sets, and CRUD operations.
 *
 * Uses ObjectMapperService for mapper resolution
 * and RequestParamsParser for request parameter parsing.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ObjectQueryService {
	/**
	 * @param ObjectMapperService $mapperService The mapper service
	 * @param RequestParamsParser $paramsParser The request parameter parser
	 */
	public function __construct(
		private readonly ObjectMapperService $mapperService,
		private readonly RequestParamsParser $paramsParser,
	) {
	}//end __construct()

	/**
	 * Gets an object by type and id.
	 *
	 * @param string $objectType The type of object
	 * @param string $id The object id, or a URL whose last segment is the id
	 * @param array $extend Relations to expand; only supported for OpenRegister objects
	 *
	 * @return mixed The serialized object, or null when it does not exist
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getObject(string $objectType, string $id, array $extend = []): mixed {
		$id = self::extractIdFromUrl(id: $id);
		$mapper = $this->mapperService->getMapper($objectType);
		self::assertExtendAllowed(mapper: $mapper, extend: $extend);

		try {
			$object = $mapper->find($id);
		} catch (DoesNotExistException $e) {
			// Object not found: signal a 404 to the controller (which treats
			// a null result as Http::STATUS_NOT_FOUND) instead of bubbling an
			// uncaught exception up to a 500.
			return null;
		}

		if ($object === null) {
			return null;
		}

		return self::serializeObject(object: $object);
	}//end getObject()

	/**
	 * Gets objects with filters, sorting, and extensions.
	 *
	 * @param string $objectType The type of object
	 * @param integer|null $limit Maximum number of objects to return, or null for no limit
	 * @param integer|null $offset Number of objects to skip, or null for none
	 * @param array|null $filters Field filters to apply
	 * @param array|null $sort Sort clauses, keyed by field
	 * @param string|null $search Free-text search string
	 * @param array|null $extend Relations to expand; only supported for OpenRegister objects
	 *
	 * @return array The serialized objects
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
		$mapper = $this->mapperService->getMapper($objectType);
		self::assertExtendAllowed(mapper: $mapper, extend: $extend);

		return array_map(
			[self::class, 'serializeObject'],
			$mapper->findAll(limit: $limit, offset: $offset, filters: $filters, sort: $sort, search: $search, extend: $extend)
		);
	}//end getObjects()

	/**
	 * Gets facets for a specific object type.
	 *
	 * Calls OpenRegister's `getFacetsForObjects()`. The previous call here was
	 * to `getAggregations()`, which exists nowhere in OpenRegister — and it sat
	 * behind an `instanceof ObjectService` guard that PASSES in production, so
	 * the only environment where the guard short-circuited was one without
	 * OpenRegister installed. In other words the guard hid the defect exactly
	 * where the app is not used, and every real faceted query raised an
	 * uncaught Error. Reached from getResultArrayForRequest() below.
	 *
	 * @param string $objectType The type of object
	 * @param array $filters Field filters narrowing the facetted set
	 *
	 * @return array The facets, or an empty array when the mapper is not OpenRegister
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getFacets(string $objectType, array $filters = []): array {
		$mapper = $this->mapperService->getMapper($objectType);
		if ($mapper instanceof \OCA\OpenRegister\Service\ObjectService) {
			return $mapper->getFacetsForObjects($filters);
		}

		return [];
	}//end getFacets()

	/**
	 * Gets all objects of a specific type.
	 *
	 * @param string $objectType The type of object
	 * @param integer|null $limit Maximum number of objects to return, or null for no limit
	 * @param integer|null $offset Number of objects to skip, or null for none
	 *
	 * @return array The objects as returned by the mapper
	 */
	public function getAllObjects(string $objectType, ?int $limit = null, ?int $offset = null): array {
		return $this->mapperService->getMapper($objectType)->findAll($limit, $offset);
	}//end getAllObjects()

	/**
	 * Creates or updates an object.
	 *
	 * @param string $objectType The type of object
	 * @param array $object The object data; an `id` key makes this an update
	 *
	 * @return mixed The created or updated object as returned by the mapper
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function saveObject(string $objectType, array $object): mixed {
		$mapper = $this->mapperService->getMapper($objectType);

		// An update always bumps the object version - passed explicitly rather
		// than relying on the mapper's own default so the intent stays visible.
		if (isset($object['id']) === true) {
			return $mapper->updateFromArray($object['id'], $object, true);
		}

		return $mapper->createFromArray($object);
	}//end saveObject()

	/**
	 * Deletes an object.
	 *
	 * @param string $objectType The type of object
	 * @param string|integer $id The object id, or a URL whose last segment is the id
	 *
	 * @return boolean True when the mapper reported a delete, false on any failure
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function deleteObject(string $objectType, string|int $id): bool {
		try {
			$id = self::extractIdFromUrl(id: $id);
			$mapper = $this->mapperService->getMapper($objectType);
			// The OR ObjectServiceMapperAdapter::delete() expects a criteria
			// array keyed by 'id' — NOT the ObjectEntity returned by find().
			// Passing the entity raises an uncaught \TypeError → 500.
			return $mapper->delete(['id' => $id]);
		} catch (\Throwable $e) {
			return false;
		}
	}//end deleteObject()

	/**
	 * Get count of objects.
	 *
	 * @param string $objectType The type of object
	 * @param array $filters Field filters narrowing the counted set
	 *
	 * @return integer The number of matching objects, or 0 when the mapper is not OpenRegister
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getCount(string $objectType, array $filters = []): int {
		$mapper = $this->mapperService->getMapper($objectType);
		if ($mapper instanceof \OCA\OpenRegister\Service\ObjectService) {
			return $mapper->count(filters: $filters);
		}

		return 0;
	}//end getCount()

	/**
	 * Get a result array for a request.
	 *
	 * @param string $objectType The type of object
	 * @param array $requestParams The raw request parameters to parse into a query
	 *
	 * @return array The response body with keys: results, facets, total
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-004
	 */
	public function getResultArrayForRequest(string $objectType, array $requestParams): array {
		$params = $this->paramsParser->parse($requestParams);

		return [
			'results' => $this->getObjects(
				objectType: $objectType,
				limit: $params['limit'],
				offset: $params['offset'],
				filters: $params['filters'],
				sort: $params['order'],
				search: $params['search'],
				extend: $params['extend']
			),
			'facets' => $this->getFacets(objectType: $objectType, filters: $params['filters']),
			'total' => $this->getCount(objectType: $objectType, filters: $params['filters']),
		];
	}//end getResultArrayForRequest()

	/**
	 * Gets multiple objects by ids.
	 *
	 * @param string $objectType The type of object
	 * @param array $ids The ids; each entry may be an id, a URL, an entity with getId(), or an array with an `id` key
	 *
	 * @return array The objects as returned by the mapper
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getMultipleObjects(string $objectType, array $ids): array {
		$cleanedIds = array_map(
			[self::class, 'extractIdFromUrl'],
			array_map(
				function ($id) {
					if (is_object($id) === true && method_exists($id, 'getId') === true) {
						return $id->getId();
					}

					if (is_array($id) === true && isset($id['id']) === true) {
						return $id['id'];
					}

					return $id;
				},
				$ids
			)
		);

		return $this->mapperService->getMapper($objectType)->findMultiple($cleanedIds);
	}//end getMultipleObjects()

	/**
	 * Call a mapper method by name for an object type and id.
	 *
	 * @param string $objectType The type of object
	 * @param string $method The mapper method to invoke
	 * @param string $id The object id passed to that method
	 *
	 * @return array The method's result
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function callMapperMethod(string $objectType, string $method, string $id): array {
		return $this->mapperService->getMapper($objectType)->$method($id);
	}//end callMapperMethod()

	/**
	 * Reduce an identifier to its bare id, accepting a full object URL.
	 *
	 * Callers may hand over a self-link such as `https://host/api/zaken/{uuid}`
	 * instead of the uuid itself; only the last path segment is the id.
	 *
	 * @param mixed $id The id, or a URL whose last path segment is the id
	 *
	 * @return mixed The bare id, or the input unchanged when it is not a URL
	 */
	private static function extractIdFromUrl(mixed $id): mixed {
		if (is_string($id) === true && filter_var($id, FILTER_VALIDATE_URL) !== false) {
			$parts = explode('/', rtrim($id, '/'));
			return end($parts);
		}

		return $id;
	}//end extractIdFromUrl()

	/**
	 * Turn a mapper result into a plain array for a JSON response.
	 *
	 * @param mixed $object The mapper result: an array, an entity, or any other value
	 *
	 * @return mixed The array representation of the object
	 */
	private static function serializeObject(mixed $object): mixed {
		if (is_array($object) === true) {
			return $object;
		}

		if (is_object($object) === true && method_exists($object, 'jsonSerialize') === true) {
			return $object->jsonSerialize();
		}

		return (array)$object;
	}//end serializeObject()

	/**
	 * Reject an `extend` request against a mapper that cannot expand relations.
	 *
	 * Only the OpenRegister ObjectService resolves relations, so asking any other
	 * mapper to extend would silently return unexpanded objects.
	 *
	 * @param mixed $mapper The resolved mapper for the object type
	 * @param array|null $extend The requested relations to expand
	 *
	 * @return void
	 * @throws InvalidArgumentException When extend is requested on a non-OpenRegister mapper.
	 */
	private static function assertExtendAllowed(mixed $mapper, ?array $extend): void {
		if (empty($extend) === false && ($mapper instanceof \OCA\OpenRegister\Service\ObjectService) === false) {
			throw new InvalidArgumentException('Extend is only available for OpenRegister objects');
		}
	}//end assertExtendAllowed()
}//end class
