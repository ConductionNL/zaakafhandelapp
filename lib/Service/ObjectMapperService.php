<?php

namespace OCA\ZaakAfhandelApp\Service;

use Exception;
use InvalidArgumentException;
use OCP\App\IAppManager;
use Psr\Container\ContainerInterface;
use OCP\IAppConfig;

/**
 * Service for resolving object type mappers from configuration.
 *
 * Handles the mapping between object types and their data sources
 * (internal mappers or OpenRegister).
 */
class ObjectMapperService
{
	/** @var string $appName The name of the app */
	private string $appName;

	/**
	 * Constructor for ObjectMapperService.
	 *
	 * @param ContainerInterface $container The DI container
	 * @param IAppManager $appManager The app manager
	 * @param IAppConfig $config The app configuration
	 */
	public function __construct(
		private ContainerInterface $container,
		private readonly IAppManager $appManager,
		private readonly IAppConfig $config,
	) {
		$this->appName = 'zaakafhandelapp';
	}

	/**
	 * Gets the appropriate mapper based on the object type.
	 *
	 * @param string $objectType The type of object
	 *
	 * @return mixed The appropriate mapper
	 * @throws InvalidArgumentException|Exception
	 */
	public function getMapper(string $objectType): mixed
	{
		$objectTypeLower = strtolower($objectType);
		$source = $this->config->getValueString($this->appName, $objectTypeLower . '_source', 'internal');

		if ($source === 'openregister') {
			return $this->getOpenRegisterMapper($objectTypeLower);
		}

		return match ($objectType) {
			default => throw new InvalidArgumentException("Unknown object type: $objectType"),
		};
	}

	/**
	 * Get an OpenRegister mapper for the given object type.
	 *
	 * @param string $objectTypeLower The lowercase object type
	 *
	 * @return mixed The OpenRegister mapper
	 * @throws Exception
	 */
	private function getOpenRegisterMapper(string $objectTypeLower): mixed
	{
		$openRegister = $this->getOpenRegisters();
		if ($openRegister === null) {
			throw new Exception("OpenRegister service not available");
		}

		$register = $this->config->getValueString($this->appName, $objectTypeLower . '_register', '');
		if (empty($register)) {
			throw new Exception("Register not configured for $objectTypeLower");
		}

		$schema = $this->config->getValueString($this->appName, $objectTypeLower . '_schema', '');
		if (empty($schema)) {
			throw new Exception("Schema not configured for $objectTypeLower");
		}

		return $openRegister->getMapper(register: $register, schema: $schema);
	}

	/**
	 * Attempts to retrieve the OpenRegister service.
	 *
	 * @return \OCA\OpenRegister\Service\ObjectService|null The service or null
	 */
	public function getOpenRegisters(): ?\OCA\OpenRegister\Service\ObjectService
	{
		if (in_array(needle: 'openregister', haystack: $this->appManager->getInstalledApps())) {
			try {
				return $this->container->get('OCA\OpenRegister\Service\ObjectService');
			} catch (Exception $e) {
				return null;
			}
		}

		return null;
	}
}
