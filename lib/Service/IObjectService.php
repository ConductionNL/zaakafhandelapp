<?php

namespace OCA\ZaakAfhandelApp\Service;

/**
 * Interface for object service operations.
 *
 * Provides a contract for CRUD and query operations on typed objects.
 */
interface IObjectService
{

    /**
     * Gets a mapper for the given object type.
     *
     * @param string $objectType The type of object
     *
     * @return mixed The mapper
     */
    public function getMapper(string $objectType): mixed;

    /**
     * Gets an object by type and id.
     *
     * @param string $objectType The type
     * @param string $id The id
     * @param array $extend Extensions
     *
     * @return mixed The object as array
     */
    public function getObject(string $objectType, string $id, array $extend = []): mixed;

    /**
     * Gets multiple objects with filtering.
     *
     * @param string $objectType The type
     * @param int|null $limit Max results
     * @param int|null $offset Offset
     * @param array|null $filters Filters
     * @param array|null $sort Sort params
     * @param string|null $search Search string
     * @param array|null $extend Extensions
     *
     * @return array Objects as arrays
     */
    public function getObjects(
        string $objectType,
        ?int $limit = null,
        ?int $offset = null,
        ?array $filters = [],
        ?array $sort = [],
        ?string $search = null,
        ?array $extend = []
    ): array;

    /**
     * Creates or updates an object.
     *
     * @param string $objectType The type
     * @param array $object The data
     * @param bool $updateVersion Whether to bump version
     *
     * @return mixed The saved object
     */
    public function saveObject(string $objectType, array $object, bool $updateVersion = true): mixed;

    /**
     * Deletes an object.
     *
     * @param string $objectType The type
     * @param string|int $id The id
     *
     * @return bool Success
     */
    public function deleteObject(string $objectType, string|int $id): bool;
}
