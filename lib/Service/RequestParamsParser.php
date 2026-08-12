<?php

namespace OCA\ZaakAfhandelApp\Service;

/**
 * Utility class for parsing request parameters into structured query parameters.
 *
 * Extracted from ObjectService to reduce class complexity.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class RequestParamsParser {

	/**
	 * Parameters that should be removed from filters.
	 */
	private const RESERVED_PARAMS = [
		'_route',
		'_extend',
		'_limit',
		'_offset',
		'_order',
		'_page',
		'_search',
		'extend',
		'limit',
		'offset',
		'order',
		'page',
		'search',
	];

	/**
	 * Parse request parameters into structured query parameters.
	 *
	 * @param array $requestParams The raw request parameters
	 *
	 * @return array Parsed parameters with keys: limit, offset, order, extend, search, filters
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-004
	 */
	public function parse(array $requestParams): array {
		$limit = $this->getParam(params: $requestParams, name: 'limit');
		$offset = $this->getParam(params: $requestParams, name: 'offset');
		$order = $this->getParam(params: $requestParams, name: 'order') ?? [];
		$extend = $this->getParam(params: $requestParams, name: 'extend');
		$page = $this->getParam(params: $requestParams, name: 'page');
		$search = $this->getParam(params: $requestParams, name: 'search');

		$offset = $this->calculateOffset(page: $page, limit: $limit, offset: $offset);
		$order = $this->ensureArray(value: $order);
		$extend = $this->ensureArray(value: $extend);

		return [
			'limit' => $limit,
			'offset' => $offset,
			'order' => $order,
			'extend' => $extend,
			'search' => $search,
			'filters' => $this->extractFilters(requestParams: $requestParams),
		];
	}//end parse()

	/**
	 * Get a parameter value, checking both prefixed and unprefixed versions.
	 *
	 * @param array $params The parameters array
	 * @param string $name The parameter name (without underscore prefix)
	 *
	 * @return mixed The parameter value or null
	 */
	private function getParam(array $params, string $name): mixed {
		return $params[$name] ?? $params['_' . $name] ?? null;
	}//end getParam()

	/**
	 * Calculate the offset from page and limit if page is set.
	 *
	 * @param mixed $page The page number
	 * @param mixed $limit The limit
	 * @param mixed $offset The current offset
	 *
	 * @return mixed The calculated offset
	 */
	private function calculateOffset(mixed $page, mixed $limit, mixed $offset): mixed {
		if ($page !== null && $limit !== null) {
			return $limit * ($page - 1);
		}

		return $offset;
	}//end calculateOffset()

	/**
	 * Ensure a value is an array (convert from comma-separated string if needed).
	 *
	 * @param mixed $value The value to convert
	 *
	 * @return mixed The value as an array, or original value if already null/array
	 */
	private function ensureArray(mixed $value): mixed {
		if (is_string($value) === true) {
			return array_map('trim', explode(',', $value));
		}

		return $value;
	}//end ensureArray()

	/**
	 * Extract filters by removing reserved parameters.
	 *
	 * @param array $requestParams The raw request parameters
	 *
	 * @return array The filter parameters
	 */
	private function extractFilters(array $requestParams): array {
		return array_diff_key($requestParams, array_flip(self::RESERVED_PARAMS));
	}//end extractFilters()
}//end class
