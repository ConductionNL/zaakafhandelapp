<?php

namespace OCA\ZaakAfhandelApp\Service;

/**
 * Utility class for parsing request parameters into structured query parameters.
 *
 * Extracted from ObjectService to reduce class complexity.
 */
class RequestParamsParser
{

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
     */
    public function parse(array $requestParams): array
    {
        $limit  = $this->getParam($requestParams, 'limit');
        $offset = $this->getParam($requestParams, 'offset');
        $order  = $this->getParam($requestParams, 'order') ?? [];
        $extend = $this->getParam($requestParams, 'extend');
        $page   = $this->getParam($requestParams, 'page');
        $search = $this->getParam($requestParams, 'search');

        $offset = $this->calculateOffset($page, $limit, $offset);
        $order  = $this->ensureArray($order);
        $extend = $this->ensureArray($extend);

        return [
            'limit'   => $limit,
            'offset'  => $offset,
            'order'   => $order,
            'extend'  => $extend,
            'search'  => $search,
            'filters' => $this->extractFilters($requestParams),
        ];
    }//end parse()

    /**
     * Get a parameter value, checking both prefixed and unprefixed versions.
     *
     * @param array  $params The parameters array
     * @param string $name   The parameter name (without underscore prefix)
     *
     * @return mixed The parameter value or null
     */
    private function getParam(array $params, string $name): mixed
    {
        return $params[$name] ?? $params['_'.$name] ?? null;
    }//end getParam()

    /**
     * Calculate the offset from page and limit if page is set.
     *
     * @param mixed $page   The page number
     * @param mixed $limit  The limit
     * @param mixed $offset The current offset
     *
     * @return mixed The calculated offset
     */
    private function calculateOffset(mixed $page, mixed $limit, mixed $offset): mixed
    {
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
    private function ensureArray(mixed $value): mixed
    {
        if (is_string($value)) {
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
    private function extractFilters(array $requestParams): array
    {
        return array_diff_key($requestParams, array_flip(self::RESERVED_PARAMS));
    }//end extractFilters()
}//end class
