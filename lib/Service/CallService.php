<?php

namespace OCA\ZaakAfhandelApp\Service;

use GuzzleHttp\Client;
use Symfony\Component\Uid\Uuid;
use OCP\IAppConfig;

class CallService
{
	public function __construct(
		IAppConfig $config,
	) {
		$this->config = $config;
	}

//	private function getOAuth (string $source): string
//	{
//		$this->config->getValueString(app: 'zaakafhandelapp', key: )
//	}

	private function getAuthorization (string $source): array
	{
		$authType = $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}AuthType");

		switch ($authType) {
//			case 'OAuth 2.0':
//				return ['headers' => ['authorization' => $this->getOAuth(source: $source)]];
			case 'basic':
				return ['auth' => [ $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}ClientId"),  $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}Secret")]];
			case 'apiKey':
				return ['headers' => ['authorization' => $this->config->getValueString(app: 'zaakafhandelapp', key: "{$source}Key")]];
			default:
				return [];
		}
	}

	/**
	 * Gets the guzzle config as an array
	 *
	 * @return array
	 */
	public function getConfig(?string $source = null, array $query = []): array
	{
		$result = [
			'base_uri' => $this->config->getValueString('zaakafhandelapp', "{$source}Location"),
			'query'   => $query,
			'headers' => [
			]
		];

		return array_merge_recursive($result, $this->getAuthorization($source));


	}

	/**
	 * Gets a guzzle client based upon given config.
	 *
	 * @param array $config The config to be used for the client.
	 * @return Client
	 */
	private function getClient(string $source, array $config = []): Client
	{
		// Add any config to the call

		$config = array_merge_recursive($config,$this->getConfig(source: $source));

		// Return the call
		return new Client($config);
	}

	/**
	 * Finds objects based upon a set of filters.
	 *
	 * @param array $query The filters to compare the object to.
	 *
	 * @return array The objects found for given filters.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function index(string $source, string $endpoint, array $query = []): array | null
	{
		$config = [
			'query'   => $query,
		];


		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source, config: $config)->get("$endpoint");

		// Turn everything into arrays
		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}

	/**
	 * Finds objects based upon a set of filters.
	 *
	 * @param array $query The filters to compare the object to.
	 * @param string $id The id of the object to get
	 *
	 * @return array The objects found for given filters.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function show(string $source, string $endpoint, string $id, array $query = []): array | null
	{
		// let add the query
		$config = [
				'query'   => $query,
		];

		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source, config: $config)->get($this->config->getValueString('zaakafhandelapp', "{$source}Location")."/$endpoint/$id");

		// Turn everything into arrays
		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}

	/**
	 * Create an object
	 *
	 * @param array $data The data to post.
	 *
	 * @return array The objects found for given filters.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function create(string $source, string $endpoint, array $data): array | null
	{
		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source)->post(uri: "$endpoint", options: ['json' => $data]);

		// Turn everything into arrays
		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}

	/**
	 * Update an object
	 *
	 * @param array $data The data to updata.
	 * @param string $id The id of the object to updata
	 *
	 * @return array The objects found for given filters.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function update(string $source, string $endpoint, array $data, string $id): array | null
	{
		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source)->put("$endpoint/$id", options: ['json' => $data]);

		// Turn everything into arrays
		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}

	/**
	 * Deletes an object
	 *
	 * @param string $id The id of the object to delete
	 *
	 * @return array The objects found for given filters.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function destroy(string $source, string $endpoint, string $id): array | null
	{
		// Setuo the client & make the call
		$returnData = $this->getClient(source: $source)->delete("$endpoint/$id");

		// Turn everything into arrays
		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}
}
