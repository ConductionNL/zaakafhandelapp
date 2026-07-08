<?php

namespace OCA\ZaakAfhandelApp\Service;

use GuzzleHttp\Client;
use Symfony\Component\Uid\Uuid;
use OCP\IAppConfig;

/**
 * Service for performing outbound HTTP calls to external ZGW sources.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class CallService
{
    public function __construct(
        private readonly IAppConfig $config,
    ) {
    }//end __construct()

    // private function getOAuth (string $source): string
    // {
    // $this->config->getValueString(app: 'zaakafhandelapp', key: )
    // }
    private function getAuthorization(string $source): array
    {
        $authType = $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}AuthType");

        switch ($authType) {
            // case 'OAuth 2.0':
            // return ['headers' => ['authorization' => $this->getOAuth(source: $source)]];
            case 'basic':
                return ['auth' => [ $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}ClientId"),  $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}Secret")]];
            case 'apiKey':
                return ['headers' => ['authorization' => $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}Key")]];
            default:
                return [];
        }
    }//end getAuthorization()

    /**
     * Gets the guzzle config as an array
     *
     * @return array
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
     */
    public function getConfig(?string $source=null, array $query=[]): array
    {
        $result = [
            'base_uri'    => $this->config->getValueString('zaakafhandelapp', "{$source}Location"),
            'query'       => $query,
            'headers'     => [],
            // Disable Guzzle's default behaviour of throwing on non-2xx responses so that
            // ZGW backend error bodies (validation details, 404 etc.) are returned to the
            // caller rather than bubbling as an unhandled GuzzleException → HTTP 500 (#282 bug-4).
            'http_errors' => false,
        ];

        return array_merge_recursive($result, $this->getAuthorization($source));

    }//end getConfig()

    /**
     * Gets a guzzle client based upon given config.
     *
     * @param  array $config The config to be used for the client.
     * @return Client
     */
    private function getClient(string $source, array $config=[]): Client
    {
        // Add any config to the call
        $config = array_merge_recursive($config, $this->getConfig(source: $source));

        // Return the call
        return new Client($config);
    }//end getClient()

    /**
     * Decode a Guzzle response body as JSON, returning null on empty or malformed content (#282 bug-4).
     *
     * @param string $body The raw response body.
     *
     * @return array|null Decoded associative array or null.
     */
    private function decodeJson(string $body): ?array
    {
        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, associative: true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }//end decodeJson()

    /**
     * Finds objects based upon a set of filters.
     *
     * @param array $query The filters to compare the object to.
     *
     * @return array The objects found for given filters.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
     */
    public function index(string $source, string $endpoint, array $query=[]): array | null
    {
        $config = [
            'query'   => $query,
        ];

        // Setuo the client & make the call
        $returnData = $this->getClient(source: $source, config: $config)->get("$endpoint");

        return $this->decodeJson($returnData->getBody()->getContents());
    }//end index()

    /**
     * Finds objects based upon a set of filters.
     *
     * @param array  $query The filters to compare the object to.
     * @param string $id    The id of the object to get
     *
     * @return array The objects found for given filters.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
     */
    public function show(string $source, string $endpoint, string $id, array $query=[]): array | null
    {
        // let add the query
        $config = [
            'query'   => $query,
        ];

        // Use a relative path so Guzzle appends it to the base_uri configured in getConfig().
        // Previously the full absolute URL was constructed here, duplicating the base_uri that
        // getClient() already sets — causing double-prefixing on the request (M2).
        $returnData = $this->getClient(source: $source, config: $config)->get("$endpoint/$id");

        return $this->decodeJson($returnData->getBody()->getContents());
    }//end show()

    /**
     * Create an object
     *
     * @param array $data The data to post.
     *
     * @return array The objects found for given filters.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
     */
    public function create(string $source, string $endpoint, array $data): array | null
    {
        // Setuo the client & make the call
        $returnData = $this->getClient(source: $source)->post(uri: "$endpoint", options: ['json' => $data]);

        return $this->decodeJson($returnData->getBody()->getContents());
    }//end create()

    /**
     * Update an object
     *
     * @param array  $data The data to updata.
     * @param string $id   The id of the object to updata
     *
     * @return array The objects found for given filters.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
     */
    public function update(string $source, string $endpoint, array $data, string $id): array | null
    {
        // Setuo the client & make the call
        $returnData = $this->getClient(source: $source)->put("$endpoint/$id", options: ['json' => $data]);

        return $this->decodeJson($returnData->getBody()->getContents());
    }//end update()

    /**
     * Deletes an object
     *
     * @param string $id The id of the object to delete
     *
     * @return array The objects found for given filters.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
     */
    public function destroy(string $source, string $endpoint, string $id): array | null
    {
        // Setuo the client & make the call
        $returnData = $this->getClient(source: $source)->delete("$endpoint/$id");

        return $this->decodeJson($returnData->getBody()->getContents());
    }//end destroy()
}//end class
