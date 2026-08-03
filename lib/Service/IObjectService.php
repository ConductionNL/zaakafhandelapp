<?php

namespace OCA\ZaakAfhandelApp\Service;

/**
 * Interface for object service operations.
 *
 * Provides a contract for CRUD and query operations on typed objects.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
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
     * @param string $id         The id
     * @param array  $extend     Extensions
     *
     * @return mixed The object as array
     */
    public function getObject(string $objectType, string $id, array $extend=[]): mixed;

    /**
     * Gets multiple objects with filtering.
     *
     * @param string       $objectType The type
     * @param integer|null $limit      Max results
     * @param integer|null $offset     Offset
     * @param array|null   $filters    Filters
     * @param array|null   $sort       Sort params
     * @param string|null  $search     Search string
     * @param array|null   $extend     Extensions
     *
     * @return array Objects as arrays
     */
    public function getObjects(
        string $objectType,
        ?int $limit=null,
        ?int $offset=null,
        ?array $filters=[],
        ?array $sort=[],
        ?string $search=null,
        ?array $extend=[]
    ): array;

    /**
     * Creates or updates an object.
     *
     * An update always bumps the object version; there is no suppress-the-bump
     * variant, because nothing in this app ever wanted one and a boolean flag
     * selecting between two behaviours belongs in two methods, not one argument.
     *
     * @param string $objectType The type
     * @param array  $object     The data
     *
     * @return mixed The saved object
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
     */
    public function saveObject(string $objectType, array $object): mixed;

    /**
     * Deletes an object.
     *
     * @param string         $objectType The type
     * @param string|integer $id         The id
     *
     * @return boolean Success
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-003
     */
    public function deleteObject(string $objectType, string|int $id): bool;
}//end interface
