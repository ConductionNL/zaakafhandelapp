<?php

namespace OCA\ZaakAfhandelApp\Service;

use Exception;
use InvalidArgumentException;

/**
 * Service for querying objects, building result sets, and CRUD operations.
 *
 * Uses ObjectMapperService for mapper resolution
 * and RequestParamsParser for request parameter parsing.
 */
class ObjectQueryService
{

    /**
     * @param ObjectMapperService $mapperService The mapper service
     * @param RequestParamsParser $paramsParser The request parameter parser
     */
    public function __construct(
        private readonly ObjectMapperService $mapperService,
        private readonly RequestParamsParser $paramsParser,
    ) {
    }

    /**
     * Gets an object by type and id.
     */
    public function getObject(string $objectType, string $id, array $extend = []): mixed
    {
        $id = self::extractIdFromUrl($id);
        $mapper = $this->mapperService->getMapper($objectType);
        self::assertExtendAllowed($mapper, $extend);

        return self::serializeObject($mapper->find($id));
    }

    /**
     * Gets objects with filters, sorting, and extensions.
     */
    public function getObjects(
        string $objectType,
        ?int $limit = null,
        ?int $offset = null,
        ?array $filters = [],
        ?array $sort = [],
        ?string $search = null,
        ?array $extend = []
    ): array {
        $mapper = $this->mapperService->getMapper($objectType);
        self::assertExtendAllowed($mapper, $extend);

        return array_map(
            [self::class, 'serializeObject'],
            $mapper->findAll(limit: $limit, offset: $offset, filters: $filters, sort: $sort, search: $search, extend: $extend)
        );
    }

    /**
     * Gets facets for a specific object type.
     */
    public function getFacets(string $objectType, array $filters = []): array
    {
        $mapper = $this->mapperService->getMapper($objectType);
        return ($mapper instanceof \OCA\OpenRegister\Service\ObjectService) ? $mapper->getAggregations($filters) : [];
    }

    /**
     * Gets all objects of a specific type.
     */
    public function getAllObjects(string $objectType, ?int $limit = null, ?int $offset = null): array
    {
        return $this->mapperService->getMapper($objectType)->findAll($limit, $offset);
    }

    /**
     * Creates or updates an object.
     */
    public function saveObject(string $objectType, array $object, bool $updateVersion = true): mixed
    {
        $mapper = $this->mapperService->getMapper($objectType);
        return isset($object['id'])
            ? $mapper->updateFromArray($object['id'], $object, $updateVersion)
            : $mapper->createFromArray($object);
    }

    /**
     * Deletes an object.
     */
    public function deleteObject(string $objectType, string|int $id): bool
    {
        try {
            $mapper = $this->mapperService->getMapper($objectType);
            $mapper->delete($mapper->find($id));
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get count of objects.
     */
    public function getCount(string $objectType, array $filters = []): int
    {
        $mapper = $this->mapperService->getMapper($objectType);
        return ($mapper instanceof \OCA\OpenRegister\Service\ObjectService) ? $mapper->count(filters: $filters) : 0;
    }

    /**
     * Get a result array for a request.
     */
    public function getResultArrayForRequest(string $objectType, array $requestParams): array
    {
        $p = $this->paramsParser->parse($requestParams);

        return [
            'results' => $this->getObjects($objectType, $p['limit'], $p['offset'], $p['filters'], $p['order'], $p['search'], $p['extend']),
            'facets' => $this->getFacets($objectType, $p['filters']),
            'total' => $this->getCount($objectType, $p['filters']),
        ];
    }

    /**
     * Gets multiple objects by ids.
     */
    public function getMultipleObjects(string $objectType, array $ids): array
    {
        $cleanedIds = array_map([self::class, 'extractIdFromUrl'], array_map(function ($id) {
            return is_object($id) && method_exists($id, 'getId') ? $id->getId() : (is_array($id) && isset($id['id']) ? $id['id'] : $id);
        }, $ids));

        return $this->mapperService->getMapper($objectType)->findMultiple($cleanedIds);
    }

    /**
     * Call a mapper method by name for an object type and id.
     */
    public function callMapperMethod(string $objectType, string $method, string $id): array
    {
        return $this->mapperService->getMapper($objectType)->$method($id);
    }

    private static function extractIdFromUrl(mixed $id): mixed
    {
        if (is_string($id) && filter_var($id, FILTER_VALIDATE_URL)) {
            $parts = explode('/', rtrim($id, '/'));
            return end($parts);
        }
        return $id;
    }

    private static function serializeObject(mixed $object): mixed
    {
        if (is_array($object)) {
            return $object;
        }
        return is_object($object) && method_exists($object, 'jsonSerialize') ? $object->jsonSerialize() : (array) $object;
    }

    private static function assertExtendAllowed(mixed $mapper, ?array $extend): void
    {
        if (!empty($extend) && !($mapper instanceof \OCA\OpenRegister\Service\ObjectService)) {
            throw new InvalidArgumentException('Extend is only available for OpenRegister objects');
        }
    }
}
