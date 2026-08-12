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
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getObject(string $objectType, string $id, array $extend = []): mixed {
		$id = self::extractIdFromUrl($id);
		$mapper = $this->mapperService->getMapper($objectType);
		self::assertExtendAllowed($mapper, $extend);

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

		return self::serializeObject($object);
	}//end getObject()

	/**
	 * Gets objects with filters, sorting, and extensions.
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
		self::assertExtendAllowed($mapper, $extend);

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
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getFacets(string $objectType, array $filters = []): array {
		$mapper = $this->mapperService->getMapper($objectType);
		return ($mapper instanceof \OCA\OpenRegister\Service\ObjectService) ? $mapper->getFacetsForObjects($filters) : [];
	}//end getFacets()

	/**
	 * Gets all objects of a specific type.
	 */
	public function getAllObjects(string $objectType, ?int $limit = null, ?int $offset = null): array {
		return $this->mapperService->getMapper($objectType)->findAll($limit, $offset);
	}//end getAllObjects()

	/**
	 * Creates or updates an object.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function saveObject(string $objectType, array $object): mixed {
		$mapper = $this->mapperService->getMapper($objectType);

		// An update always bumps the object version - passed explicitly rather
		// than relying on the mapper's own default so the intent stays visible.
		return isset($object['id']) ? $mapper->updateFromArray($object['id'], $object, true) : $mapper->createFromArray($object);
	}//end saveObject()

	/**
	 * Deletes an object.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
	 */
	public function deleteObject(string $objectType, string|int $id): bool {
		try {
			$id = self::extractIdFromUrl($id);
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
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getCount(string $objectType, array $filters = []): int {
		$mapper = $this->mapperService->getMapper($objectType);
		return ($mapper instanceof \OCA\OpenRegister\Service\ObjectService) ? $mapper->count(filters: $filters) : 0;
	}//end getCount()

	/**
	 * Get a result array for a request.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-004
	 */
	public function getResultArrayForRequest(string $objectType, array $requestParams): array {
		$params = $this->paramsParser->parse($requestParams);

		return [
			'results' => $this->getObjects($objectType, $params['limit'], $params['offset'], $params['filters'], $params['order'], $params['search'], $params['extend']),
			'facets' => $this->getFacets($objectType, $params['filters']),
			'total' => $this->getCount($objectType, $params['filters']),
		];
	}//end getResultArrayForRequest()

	/**
	 * Gets multiple objects by ids.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function getMultipleObjects(string $objectType, array $ids): array {
		$cleanedIds = array_map(
			[self::class, 'extractIdFromUrl'],
			array_map(
				function ($id) {
					return is_object($id) && method_exists($id, 'getId') ? $id->getId() : (is_array($id) && isset($id['id']) ? $id['id'] : $id);
				},
				$ids
			)
		);

		return $this->mapperService->getMapper($objectType)->findMultiple($cleanedIds);
	}//end getMultipleObjects()

	/**
	 * Call a mapper method by name for an object type and id.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-002
	 */
	public function callMapperMethod(string $objectType, string $method, string $id): array {
		return $this->mapperService->getMapper($objectType)->$method($id);
	}//end callMapperMethod()

	private static function extractIdFromUrl(mixed $id): mixed {
		if (is_string($id) && filter_var($id, FILTER_VALIDATE_URL)) {
			$parts = explode('/', rtrim($id, '/'));
			return end($parts);
		}

		return $id;
	}//end extractIdFromUrl()

	private static function serializeObject(mixed $object): mixed {
		if (is_array($object)) {
			return $object;
		}

		return is_object($object) && method_exists($object, 'jsonSerialize') ? $object->jsonSerialize() : (array)$object;
	}//end serializeObject()

	private static function assertExtendAllowed(mixed $mapper, ?array $extend): void {
		if (!empty($extend) && !($mapper instanceof \OCA\OpenRegister\Service\ObjectService)) {
			throw new InvalidArgumentException('Extend is only available for OpenRegister objects');
		}
	}//end assertExtendAllowed()
}//end class
