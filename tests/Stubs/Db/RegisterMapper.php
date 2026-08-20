<?php

/**
 * Test stub for OCA\OpenRegister\Db\RegisterMapper.
 *
 * Fallback used only when the real OpenRegister app is NOT loaded. Provides the
 * findAll()/find() signatures the ObjectMapperService calls so PHPUnit can mock
 * the mapper. No-op when the real class exists.
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
 * Stub for RegisterMapper.
 */
abstract class RegisterMapper {
	/**
	 * Find all registers.
	 *
	 * @param integer|null $limit Limit.
	 * @param integer|null $offset Offset.
	 * @param array|null $filters Filters.
	 * @param array|null $searchConditions Search conditions.
	 * @param array|null $searchParams Search params.
	 * @param boolean|null $published Published flag.
	 * @param boolean $_rbac RBAC flag.
	 * @param boolean $_multitenancy Multitenancy flag.
	 *
	 * @return array<int,mixed>
	 */
	abstract public function findAll(?int $limit = null, ?int $offset = null, ?array $filters = [], ?array $searchConditions = [], ?array $searchParams = [], ?bool $published = null, bool $_rbac = true, bool $_multitenancy = true): array;

	/**
	 * Find a register by id.
	 *
	 * @param string|integer $id The register id.
	 *
	 * @return mixed
	 */
	abstract public function find(string|int $id);
}//end class
