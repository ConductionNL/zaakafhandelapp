<?php

namespace OCA\ZaakAfhandelApp\Service;

use GuzzleHttp\Client;
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
class CallService {
	/**
	 * Constructor for CallService.
	 *
	 * @param IAppConfig $config App config used to read the per-source location and credentials.
	 */
	public function __construct(
		private readonly IAppConfig $config,
	) {
	}//end __construct()

	/**
	 * Builds the Guzzle authentication options for a configured source.
	 *
	 * The auth type is read from the "{source}AuthType" app-config key. Only 'basic'
	 * and 'apiKey' are supported; an 'OAuth 2.0' type is not implemented yet and falls
	 * through to the default, which sends no credentials at all.
	 *
	 * @param string $source The configured source name, used as the app-config key prefix.
	 *
	 * @return array The Guzzle request options carrying the credentials, empty when unauthenticated.
	 */
	private function getAuthorization(string $source): array {
		$authType = $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}AuthType");

		switch ($authType) {
			case 'basic':
				return [
					'auth' => [
						$this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}ClientId"),
						$this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}Secret"),
					],
				];
			case 'apiKey':
				return ['headers' => ['authorization' => $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}Key")]];
			default:
				return [];
		}
	}//end getAuthorization()

	/**
	 * Gets the guzzle config as an array
	 *
	 * @param string|null $source The configured source name to build the config for.
	 * @param array $query Query parameters to send with every request made by the client.
	 *
	 * @return array The Guzzle client configuration for the given source.
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
	 */
	public function getConfig(?string $source = null, array $query = []): array {
		$result = [
			'base_uri' => $this->config->getValueString('zaakafhandelapp', "{$source}Location"),
			'query' => $query,
			'headers' => [],
			// Disable Guzzle's default behaviour of throwing on non-2xx responses so that
			// ZGW backend error bodies (validation details, 404 etc.) are returned to the
			// caller rather than bubbling as an unhandled GuzzleException → HTTP 500 (#282 bug-4).
			'http_errors' => false,
		];

		return array_merge_recursive($result, $this->getAuthorization(source: $source));
	}//end getConfig()

	/**
	 * Gets a guzzle client based upon given config.
	 *
	 * @param string $source The configured source name the client should talk to.
	 * @param array $config Extra client config, merged on top of the source config.
	 *
	 * @return Client The configured Guzzle client.
	 */
	private function getClient(string $source, array $config = []): Client {
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
	private function decodeJson(string $body): ?array {
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
	 * @param string $source The configured source name to call.
	 * @param string $endpoint The collection endpoint, relative to the source location.
	 * @param array $query The filters to compare the object to.
	 *
	 * @return array|null The objects found for given filters, or null on an empty or malformed body.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
	 */
	public function index(string $source, string $endpoint, array $query = []): ?array {
		$config = [
			'query' => $query,
		];

		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source, config: $config)->get("$endpoint");

		return $this->decodeJson(body: $returnData->getBody()->getContents());
	}//end index()

	/**
	 * Finds a single object by its id.
	 *
	 * @param string $source The configured source name to call.
	 * @param string $endpoint The collection endpoint, relative to the source location.
	 * @param string $id The id of the object to get
	 * @param array $query The filters to compare the object to.
	 *
	 * @return array|null The object found for the given id, or null on an empty or malformed body.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
	 */
	public function show(string $source, string $endpoint, string $id, array $query = []): ?array {
		// Add the query to the client config
		$config = [
			'query' => $query,
		];

		// Use a relative path so Guzzle appends it to the base_uri configured in getConfig().
		// Previously the full absolute URL was constructed here, duplicating the base_uri that
		// getClient() already sets — causing double-prefixing on the request (M2).
		$returnData = $this->getClient(source: $source, config: $config)->get("$endpoint/$id");

		return $this->decodeJson(body: $returnData->getBody()->getContents());
	}//end show()

	/**
	 * Create an object
	 *
	 * @param string $source The configured source name to call.
	 * @param string $endpoint The collection endpoint, relative to the source location.
	 * @param array $data The data to post.
	 *
	 * @return array|null The created object, or null on an empty or malformed body.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
	 */
	public function create(string $source, string $endpoint, array $data): ?array {
		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source)->post(uri: "$endpoint", options: ['json' => $data]);

		return $this->decodeJson(body: $returnData->getBody()->getContents());
	}//end create()

	/**
	 * Update an object
	 *
	 * @param string $source The configured source name to call.
	 * @param string $endpoint The collection endpoint, relative to the source location.
	 * @param array $data The data to updata.
	 * @param string $id The id of the object to updata
	 *
	 * @return array|null The updated object, or null on an empty or malformed body.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
	 */
	public function update(string $source, string $endpoint, array $data, string $id): ?array {
		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source)->put("$endpoint/$id", options: ['json' => $data]);

		return $this->decodeJson(body: $returnData->getBody()->getContents());
	}//end update()

	/**
	 * Deletes an object
	 *
	 * @param string $source The configured source name to call.
	 * @param string $endpoint The collection endpoint, relative to the source location.
	 * @param string $id The id of the object to delete
	 *
	 * @return array|null The response body of the delete call, or null when it is empty or malformed.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 *
	 * @spec openspec/specs/zgw-object-data-access/spec.md#REQ-005
	 */
	public function destroy(string $source, string $endpoint, string $id): ?array {
		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source)->delete("$endpoint/$id");

		return $this->decodeJson(body: $returnData->getBody()->getContents());
	}//end destroy()
}//end class
