<?php

namespace OCA\ZaakAfhandelApp\Service;

use GuzzleHttp\Client;
use Symfony\Component\Uid\Uuid;
use OCP\IAppConfig;

class KlantenService
{
	public function __construct(
		IAppConfig $config,
	) {
		$this->config = $config;
	}

	/**
	 * Gets the guzzle config as an array
	 * 
	 * @return array
	 */
	public function getConfig(): array
	{
		return $config = [
			"base_url"=> $this->config->getValueString('zaakafhandelapp', 'klantenLocation'),
			"defaults"=>[
				'headers' => [
					"Authorization" => $this->config->getValueString('zaakafhandelapp', 'klantenKey')
				]
			]
		];

	}

	/**
	 * Gets a guzzle client based upon given config.
	 *
	 * @param array $config The config to be used for the client.
	 * @return Client
	 */
	private function getClient(array $config): Client
	{
		// Add any config to the call
		$config = array_merge_recursive($config,$this->getConfig());

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
	public function index(array $query): array | null
	{
		// let add the query
		$config = [ 
			'defaults' => [
				'query'   => $query,
			]
		];

		// Setuo the client & make the call
		$returnData = $this->getClient($config)->get($this->config->getValueString('zaakafhandelapp', 'klantenLocation'));

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
	public function show(array $query, string $id): array | null
	{
		// let add the query
		$config = [ 
			'defaults' => [
				'query'   => $query,
			]
		];

		// Setuo the client & make the call
		$returnData = $this->getClient($config)->get($this->config->getValueString('zaakafhandelapp', 'klantenLocation').'/'.$id);

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
	public function create(array $data): array | null
	{
		// Setuo the client & make the call
		$returnData = $this->getClient([])->post($this->config->getValueString('zaakafhandelapp', 'klantenLocation'), $data);

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
	public function update(array $data, string $id): array | null
	{
		// Setuo the client & make the call
		$returnData = $this->getClient([])->put($this->config->getValueString('zaakafhandelapp', 'klantenLocation').'/'.$id, $data);

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
	public function destroy(string $id): array | null
	{
		// Setuo the client & make the call
		$returnData = $this->getClient([])->delete($this->config->getValueString('zaakafhandelapp', 'klantenLocation').'/'.$id);

		// Turn everything into arrays
		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}
}
