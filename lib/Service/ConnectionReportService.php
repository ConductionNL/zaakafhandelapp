<?php

/**
 * Zaakafhandelapp connection report service.
 *
 * Tells integriq's connection registry what only zaakafhandelapp can see about
 * its ZGW connections: what the last ZRC or BRC call met, and which connection
 * a configuration save touched. Integriq owns the rows the Integrations page
 * lists and works out each status itself (hydra change connection-registry,
 * design D4). Zaakafhandelapp reports, and asks for a fresh resolve after a
 * save.
 *
 * @category Service
 * @package  OCA\ZaakAfhandelApp\Service
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://conduction.nl
 *
 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Service;

use OCA\ZaakAfhandelApp\AppInfo\Application;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IAppConfig;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Sends connection reports and refresh requests to integriq.
 *
 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
 */
class ConnectionReportService {

	/**
	 * Integriq's report event (ADR-041). Named by string so zaakafhandelapp
	 * stays installable without integriq: the class is only there when
	 * integriq is.
	 *
	 * @var string
	 */
	public const STATUS_EVENT = 'OCA\Integriq\Event\ConnectionStatusReportedEvent';

	/**
	 * Integriq's refresh event. Same reason for the string as above.
	 *
	 * @var string
	 */
	public const REFRESH_EVENT = 'OCA\Integriq\Event\ConnectionRefreshRequestedEvent';

	/**
	 * The sources CallService calls, with the name a message uses for each.
	 * A unit test keeps the keys equal to the connections that
	 * `lib/Settings/connections.json` declares with `requiredConfig`.
	 *
	 * @var array<string, string>
	 */
	public const REPORTED_SOURCES = [
		'zrc' => 'ZRC',
		'brc' => 'BRC',
	];

	/**
	 * App-config keys per connection whose save asks integriq to resolve that
	 * connection again: the declared `requiredConfig`, plus the two keys that
	 * change what a call meets.
	 *
	 * @var array<string, array<int, string>>
	 */
	public const REFRESH_KEYS = [
		'zrc' => ['zrcLocation', 'zrcAuthType', 'zrcKey'],
		'brc' => ['brcLocation', 'brcAuthType', 'brcKey'],
	];

	/**
	 * HTTP statuses that say the API refused the key. They are about the
	 * connection, not about one request.
	 *
	 * @var array<int, int>
	 */
	public const REFUSED_STATUSES = [401, 403];

	/**
	 * HTTP statuses that say a gateway could not reach the API. Also about the
	 * connection: a 500 is left out, because it is often about one request.
	 *
	 * @var array<int, int>
	 */
	public const GATEWAY_ERROR_STATUSES = [502, 503, 504];

	/**
	 * Prefix of the app-config key that remembers the last report per source.
	 *
	 * @var string
	 */
	public const MEMORY_KEY_PREFIX = 'connection_report_';

	/**
	 * Seconds after which the same status is reported again.
	 *
	 * @var int
	 */
	public const REPEAT_SECONDS = 3600;

	/**
	 * Seconds that must pass before a different status is reported.
	 *
	 * @var int
	 */
	public const CHANGE_SECONDS = 300;

	/**
	 * Constructor.
	 *
	 * @param IEventDispatcher $eventDispatcher Sends the integriq events (ADR-041).
	 * @param IAppConfig       $appConfig       Reads the source locations and keeps the report memory.
	 * @param ITimeFactory     $timeFactory     Tells the time for the report memory.
	 * @param LoggerInterface  $logger          Records what could not be sent.
	 */
	public function __construct(
		private readonly IEventDispatcher $eventDispatcher,
		private readonly IAppConfig $appConfig,
		private readonly ITimeFactory $timeFactory,
		private readonly LoggerInterface $logger,
	) {
	}//end __construct()

	/**
	 * Report what one ZRC or BRC call met, when the report memory allows it.
	 *
	 * Never throws: this runs inside Guzzle's transfer, and a report that
	 * cannot be sent must not fail the call. Without integriq nothing is read,
	 * stored, sent or logged.
	 *
	 * @param string   $source     The CallService source, such as `zrc`.
	 * @param int|null $httpStatus The answer's HTTP status, or null when nothing answered.
	 *
	 * @return bool True when a report was sent.
	 *
	 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
	 */
	public function reportCall(string $source, ?int $httpStatus): bool {
		if (array_key_exists($source, self::REPORTED_SOURCES) === false) {
			return false;
		}

		$eventClass = $this->resolveEventClass(eventClass: self::STATUS_EVENT);
		if ($eventClass === null) {
			return false;
		}

		try {
			[$status, $message] = $this->observe(source: $source, httpStatus: $httpStatus);
			$now = $this->timeFactory->getTime();
			if ($this->isDue(source: $source, status: $status, now: $now) === false) {
				return false;
			}

			$sent = $this->send(
				key: $source,
				build: static fn (): object => new $eventClass(
					app: Application::APP_ID,
					key: $source,
					status: $status,
					message: $message,
				)
			);
			if ($sent === true) {
				$this->appConfig->setValueString(Application::APP_ID, self::MEMORY_KEY_PREFIX . $source, $status . '|' . $now);
			}

			return $sent;
		} catch (Throwable $e) {
			$this->logger->warning(
				'ZaakAfhandelApp: could not report a connection to integriq',
				['key' => $source, 'exception' => $e->getMessage()]
			);
			return false;
		}
	}//end reportCall()

	/**
	 * What a call's outcome says about the connection, as a status and a message.
	 *
	 * The message names the host from the admin's own setting and nothing
	 * from the request, so no case data reaches the row.
	 *
	 * @param string   $source     The CallService source, such as `zrc`.
	 * @param int|null $httpStatus The answer's HTTP status, or null when nothing answered.
	 *
	 * @return array{0: string, 1: string} The status and the message.
	 *
	 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
	 */
	public function observe(string $source, ?int $httpStatus): array {
		$name     = (self::REPORTED_SOURCES[$source] ?? strtoupper($source));
		$location = trim($this->appConfig->getValueString(Application::APP_ID, $source . 'Location', ''));
		if ($location === '') {
			return ['unconfigured', 'No ' . $name . ' address is set. Set ' . $source . 'Location to reach it.'];
		}

		$host  = parse_url($location, PHP_URL_HOST);
		$where = $name;
		if (is_string($host) === true && $host !== '') {
			$where = $name . ' at ' . $host;
		}

		if ($httpStatus === null) {
			return ['error', 'The last call to the ' . $where . ' got no answer.'];
		}

		if (in_array($httpStatus, self::REFUSED_STATUSES, true) === true) {
			return ['error', 'The ' . $where . ' refused the key (HTTP ' . $httpStatus . ').'];
		}

		if (in_array($httpStatus, self::GATEWAY_ERROR_STATUSES, true) === true) {
			return ['error', 'The ' . $where . ' answered HTTP ' . $httpStatus . ' on the last call.'];
		}

		return ['configured', 'The ' . $where . ' answered the last call.'];
	}//end observe()

	/**
	 * Ask integriq to resolve every connection whose keys the save wrote.
	 *
	 * Clears that connection's report memory too, so the next call reports at
	 * once instead of waiting out the hour. Integriq reads the saved values
	 * itself and decides the status (design D6). Never throws.
	 *
	 * @param array<int, string> $savedKeys The app-config keys the save wrote.
	 *
	 * @return array<int, string> The connection keys a refresh was sent for.
	 *
	 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
	 */
	public function refreshFromSave(array $savedKeys): array {
		$eventClass = $this->resolveEventClass(eventClass: self::REFRESH_EVENT);
		if ($eventClass === null) {
			return [];
		}

		$refreshed = [];
		foreach (self::REFRESH_KEYS as $key => $configKeys) {
			if (array_intersect($configKeys, $savedKeys) === []) {
				continue;
			}

			$this->forget(source: $key);
			$sent = $this->send(
				key: $key,
				build: static fn (): object => new $eventClass(
					app: Application::APP_ID,
					key: $key,
				)
			);
			if ($sent === true) {
				$refreshed[] = $key;
			}
		}

		return $refreshed;
	}//end refreshFromSave()

	/**
	 * The event class to instantiate, or null when integriq does not ship it.
	 *
	 * @param string $eventClass The fully qualified class name, without a leading backslash.
	 *
	 * @return string|null The class name to instantiate, or null when absent.
	 *
	 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
	 */
	protected function resolveEventClass(string $eventClass): ?string {
		$qualified = '\\' . $eventClass;
		if (class_exists($qualified) === false) {
			return null;
		}

		return $qualified;
	}//end resolveEventClass()

	/**
	 * Whether the report memory allows a report with this status now.
	 *
	 * A different status waits five minutes after the last report, so two
	 * endpoints that disagree cannot report on every request. The same status
	 * reports again after an hour.
	 *
	 * @param string $source The CallService source.
	 * @param string $status The status the call observed.
	 * @param int    $now    The current Unix time.
	 *
	 * @return bool
	 */
	private function isDue(string $source, string $status, int $now): bool {
		$memory = $this->appConfig->getValueString(Application::APP_ID, self::MEMORY_KEY_PREFIX . $source, '');
		$parts  = explode('|', $memory, 2);
		if (count($parts) !== 2 || ctype_digit($parts[1]) === false) {
			return true;
		}

		$elapsed = ($now - (int) $parts[1]);
		if ($parts[0] === $status) {
			return $elapsed >= self::REPEAT_SECONDS;
		}

		return $elapsed >= self::CHANGE_SECONDS;
	}//end isDue()

	/**
	 * Clear the report memory of one source.
	 *
	 * @param string $source The CallService source.
	 *
	 * @return void
	 */
	private function forget(string $source): void {
		try {
			$this->appConfig->deleteKey(Application::APP_ID, self::MEMORY_KEY_PREFIX . $source);
		} catch (Throwable $e) {
			$this->logger->warning(
				'ZaakAfhandelApp: could not clear a connection report memory',
				['key' => $source, 'exception' => $e->getMessage()]
			);
		}
	}//end forget()

	/**
	 * Build and dispatch one event, swallowing anything a listener throws.
	 *
	 * @param string             $key   The connection the event is about, for the log.
	 * @param callable(): object $build Builds the event.
	 *
	 * @return bool True when the event was dispatched without an exception.
	 */
	private function send(string $key, callable $build): bool {
		try {
			$event = $build();
			if (($event instanceof Event) === false) {
				return false;
			}

			$this->eventDispatcher->dispatchTyped($event);
			return true;
		} catch (Throwable $e) {
			$this->logger->warning(
				'ZaakAfhandelApp: could not send a connection event to integriq',
				['key' => $key, 'exception' => $e->getMessage()]
			);
			return false;
		}
	}//end send()
}//end class
