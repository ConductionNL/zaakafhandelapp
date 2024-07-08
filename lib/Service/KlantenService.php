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
		$config = array_merge($this->getConfig(),$config);
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
	public function index(array $query): array
	{
		// let add the query
		$config = [ 
			'defaults' => [
				'query'   => $query,
			]
		];

		// Setuo the client & make the call
		$returnData = $this->getClient($config)->get();

		// Turn everything into arrays
		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);
	}


	/**
	 * Save an object to MongoDB
	 *
	 * @param array $data	The data to be saved.
	 * @param array $config The configuration that should be used by the call.
	 *
	 * @return array The resulting object.
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function saveObject(array $data, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object 			      = self::BASE_OBJECT;
		$object['dataSource']     = $config['mongodbCluster'];
		$object['document']       = $data;
		$object['document']['id'] = $object['document']['_id'] = Uuid::v4();

		$result = $client->post(
			uri: 'action/insertOne',
			options: ['json' => $object],
		);
		$resultData =  json_decode(
			json: $result->getBody()->getContents(),
			associative: true
		);
		$id = $resultData['insertedId'];

		return $this->findObject(filters: ['_id' => $id], config: $config);
	}


	/**
	 * Finds an object based upon a set of filters (usually the id)
	 *
	 * @param array $filters The filters to compare the objects to.
	 * @param array $config  The config to be used by the call.
	 *
	 * @return array The resulting object.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function findObject(array $filters, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object               = self::BASE_OBJECT;
		$object['filter']     = $filters;
		$object['dataSource'] = $config['mongodbCluster'];

		$returnData = $client->post(
			uri: 'action/findOne',
			options: ['json' => $object]
		);

		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		)['document'];
	}

	/**
	 * Updates an object in MongoDB
	 *
	 * @param array $filters The filter to search the object with (id)
	 * @param array $update  The fields that should be updated.
	 * @param array $config  The configuration to be used by the call.
	 *
	 * @return array The updated object.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function updateObject(array $filters, array $update, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object                   = self::BASE_OBJECT;
		$object['filter']         = $filters;
		$object['update']['$set'] = $update;
		$object['dataSource']     = $config['mongodbCluster'];

		$returnData = $client->post(
			uri: 'action/updateOne',
			options: ['json' => $object]
		);

		return $this->findObject($filters, $config);
	}

	/**
	 * Delete an object according to a filter (id specifically)
	 *
	 * @param array $filters The filters to use.
	 * @param array $config  The config to be used by the call.
	 *
	 * @return array An empty array.
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deleteObject(array $filters, array $config): array
	{
		$client = $this->getClient(config: $config);

		$object                   = self::BASE_OBJECT;
		$object['filter']         = $filters;
		$object['dataSource']     = $config['mongodbCluster'];

		$returnData = $client->post(
			uri: 'action/deleteOne',
			options: ['json' => $object]
		);

		return [];
	}

	/**
	 * Aggregates objects for search facets.
	 *
	 * @param array $filters  The filters apply to the search request.
	 * @param array $pipeline The pipeline to use.
	 * @param array $config   The configuration to use in the call.
	 * @return array
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function aggregateObjects(array $filters, array $pipeline, array $config):array
	{
		$client = $this->getClient(config: $config);

		$object               = self::BASE_OBJECT;
		$object['filter']     = $filters;
		$object['pipeline']   = $pipeline;
		$object['dataSource'] = $config['mongodbCluster'];

		$returnData = $client->post(
			uri: 'action/aggregate',
			options: ['json' => $object]
		);

		return json_decode(
			json: $returnData->getBody()->getContents(),
			associative: true
		);

	}

}
