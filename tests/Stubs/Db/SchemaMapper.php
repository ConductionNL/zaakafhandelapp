<?php

/**
 * Test stub for OCA\OpenRegister\Db\SchemaMapper.
 *
 * Fallback used only when the real OpenRegister app is NOT loaded. Provides the
 * find() signature the ObjectMapperService calls so PHPUnit can mock the
 * mapper. No-op when the real class exists.
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

namespace OCA\OpenRegister\Db;

/**
 * Stub for SchemaMapper.
 */
abstract class SchemaMapper
{
    /**
     * Find a schema by id.
     *
     * @param string|integer $id            The schema id.
     * @param array|null     $_extend       Properties to extend.
     * @param boolean|null   $published     Published flag.
     * @param boolean        $_rbac         RBAC flag.
     * @param boolean        $_multitenancy Multitenancy flag.
     *
     * @return mixed
     */
    abstract public function find(string|int $id, ?array $_extend=[], ?bool $published=null, bool $_rbac=true, bool $_multitenancy=true);
}//end class
