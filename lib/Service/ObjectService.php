<?php

namespace OCA\ZaakAfhandelApp\Service;

use Exception;
use InvalidArgumentException;

/**
 * Facade service for object operations used by controllers.
 *
 * Delegates mapper resolution to ObjectMapperService and
 * query operations to ObjectQueryService.
 */
class ObjectService implements IObjectService
{
    /**
     * Constructor for ObjectService.
     *
     * @param ObjectMapperService $mapperService The mapper resolution service
     * @param ObjectQueryService  $queryService  The query operations service
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
    public function getMapper(string $objectType): mixed
    {
        return $this->mapperService->getMapper($objectType);
    }//end getMapper()

    /**
     * Attempts to retrieve the OpenRegister service.
     *
     * @return \OCA\OpenRegister\Service\ObjectService|null The service or null
     */
    public function getOpenRegisters(): ?\OCA\OpenRegister\Service\ObjectService
    {
        return $this->mapperService->getOpenRegisters();
    }//end getOpenRegisters()

    /**
     * Gets an object based on the object type and id.
     *
     * @param string $objectType The type of object
     * @param string $id         The id
     * @param array  $extend     Extensions to apply
     *
     * @return mixed The retrieved object as array
     */
    public function getObject(string $objectType, string $id, array $extend=[]): mixed
    {
        return $this->queryService->getObject($objectType, $id, $extend);
    }//end getObject()

    /**
     * Gets objects based on the object type and parameters.
     *
     * @param string       $objectType The type
     * @param integer|null $limit      Max objects
     * @param integer|null $offset     Offset
     * @param array|null   $filters    Filters
     * @param array|null   $sort       Sort params
     * @param string|null  $search     Search string
     * @param array|null   $extend     Extensions
     *
     * @return array The retrieved objects as arrays
     */
    public function getObjects(
        string $objectType,
        ?int $limit=null,
        ?int $offset=null,
        ?array $filters=[],
        ?array $sort=[],
        ?string $search=null,
        ?array $extend=[]
    ): array {
        return $this->queryService->getObjects($objectType, $limit, $offset, $filters, $sort, $search, $extend);
    }//end getObjects()

    /**
     * Gets facets for a specific object type.
     */
    public function getFacets(string $objectType, array $filters=[]): array
    {
        return $this->queryService->getFacets($objectType, $filters);
    }//end getFacets()

    /**
     * Gets all objects of a specific type.
     */
    public function getAllObjects(string $objectType, ?int $limit=null, ?int $offset=null): array
    {
        return $this->queryService->getAllObjects($objectType, $limit, $offset);
    }//end getAllObjects()

    /**
     * Creates or updates an object.
     */
    public function saveObject(string $objectType, array $object, bool $updateVersion=true): mixed
    {
        return $this->queryService->saveObject($objectType, $object, $updateVersion);
    }//end saveObject()

    /**
     * Deletes an object.
     */
    public function deleteObject(string $objectType, string|int $id): bool
    {
        return $this->queryService->deleteObject($objectType, $id);
    }//end deleteObject()

    /**
     * Get the count of objects for a given type.
     */
    public function getCount(string $objectType, array $filters=[]): int
    {
        return $this->queryService->getCount($objectType, $filters);
    }//end getCount()

    /**
     * Get a result array for a request.
     */
    public function getResultArrayForRequest(string $objectType, array $requestParams): array
    {
        return $this->queryService->getResultArrayForRequest($objectType, $requestParams);
    }//end getResultArrayForRequest()

    /**
     * Gets multiple objects by ids.
     */
    public function getMultipleObjects(string $objectType, array $ids): array
    {
        return $this->queryService->getMultipleObjects($objectType, $ids);
    }//end getMultipleObjects()

    /**
     * Get relations for an object.
     */
    public function getRelations(string $objectType, string $id): array
    {
        return $this->queryService->callMapperMethod($objectType, 'getRelations', $id);
    }//end getRelations()

    /**
     * Get uses for an object.
     */
    public function getUses(string $objectType, string $id): array
    {
        return $this->queryService->callMapperMethod($objectType, 'getUses', $id);
    }//end getUses()

    /**
     * Get audit trail for an object.
     */
    public function getAuditTrail(string $objectType, string $id): array
    {
        return $this->queryService->callMapperMethod($objectType, 'getAuditTrail', $id);
    }//end getAuditTrail()
}//end class
