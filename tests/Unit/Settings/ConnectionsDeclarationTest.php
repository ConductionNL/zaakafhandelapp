<?php

/**
 * The connection declaration integriq reads.
 *
 * `lib/Settings/connections.json` is static JSON that integriq turns into the
 * rows of zaakafhandelapp's Integrations page. Nothing in zaakafhandelapp reads
 * it at runtime, so a broken file fails nowhere in this repo: integriq skips it
 * whole and the page goes empty on some other instance. Every assertion here is
 * a way that file could go wrong without a sound.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Settings
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://conduction.nl
 *
 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-001-zaakafhandelapp-declares-its-outside-connections-in-one-static-file
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Settings;

use OCA\ZaakAfhandelApp\Service\ConnectionReportService;
use PHPUnit\Framework\TestCase;

/**
 * Guards lib/Settings/connections.json against design D2 of connection-registry.
 *
 * The rules mirror integriq's `lib/Settings/connections.schema.json` field for
 * field, including the hydra#673 amendments (`adapter.jsonPath`,
 * `adapter.simulatedValues`, `reportedOnly`). That schema is not a dependency
 * of this repo, so the rules are restated here. The file was also validated
 * against the schema itself, fetched from integriq `development`, when this
 * test was written.
 *
 * @coversNothing
 */
class ConnectionsDeclarationTest extends TestCase {

	/**
	 * The fields the schema allows on one connection, with their JSON type.
	 *
	 * @var array<string, string>
	 */
	private const FIELD_TYPES = [
		'key'                 => 'string',
		'title'               => 'string',
		'description'         => 'string',
		'order'               => 'integer',
		'settingsUrl'         => 'string',
		'requiredConfig'      => 'array',
		'adapter'             => 'array',
		'reportedOnly'        => 'boolean',
		'available'           => 'boolean',
		'unavailableMessage'  => 'string',
		'unconfiguredMessage' => 'string',
		'sourceTemplate'      => 'string',
	];

	/**
	 * The sources CallService is called with. Each must be a usable connection.
	 *
	 * @var array<int, string>
	 */
	private const CALLED_SOURCES = ['zrc', 'brc'];

	/**
	 * The sources ConfigurationController saves and nothing reads.
	 *
	 * @var array<int, string>
	 */
	private const UNUSED_SOURCES = ['drc', 'ztc', 'orc', 'klanten', 'elastic', 'mongodb'];

	/**
	 * The repository root.
	 *
	 * @return string
	 */
	private function root(): string {
		return dirname(__DIR__, 3);
	}//end root()

	/**
	 * The raw declaration file.
	 *
	 * @return string
	 */
	private function raw(): string {
		$raw = file_get_contents($this->root() . '/lib/Settings/connections.json');
		$this->assertIsString(actual: $raw, message: 'lib/Settings/connections.json must exist');

		return $raw;
	}//end raw()

	/**
	 * The decoded declaration.
	 *
	 * @return array<string, mixed>
	 */
	private function declaration(): array {
		$decoded = json_decode($this->raw(), true, 512, JSON_THROW_ON_ERROR);
		$this->assertIsArray(actual: $decoded);

		return $decoded;
	}//end declaration()

	/**
	 * The declared connections, keyed by connection key.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function connectionsByKey(): array {
		$byKey = [];
		foreach ($this->declaration()['connections'] as $connection) {
			$byKey[(string) $connection['key']] = $connection;
		}

		return $byKey;
	}//end connectionsByKey()

	/**
	 * The file names the app it ships in, and nothing else at the top level.
	 *
	 * Integriq refuses a file whose `app` differs from the app it was read from.
	 *
	 * @return void
	 */
	public function testTheFileNamesThisApp(): void {
		$declaration = $this->declaration();
		$infoXml     = simplexml_load_file($this->root() . '/appinfo/info.xml');

		$this->assertNotFalse(condition: $infoXml);
		$this->assertSame(expected: (string) $infoXml->id, actual: $declaration['app']);
		$this->assertSame(expected: ['app', 'connections'], actual: array_keys($declaration));
	}//end testTheFileNamesThisApp()

	/**
	 * Every key is unique, and the keys are the sources the configuration saves.
	 *
	 * A row is keyed by app and key, so a second entry with the same key would
	 * overwrite the first, and a renamed key orphans a row.
	 *
	 * @return void
	 */
	public function testTheKeysAreUniqueAndTheSavedSources(): void {
		$keys = array_column($this->declaration()['connections'], 'key');

		$this->assertSame(expected: array_values(array_unique($keys)), actual: $keys);
		$this->assertSame(expected: array_merge(self::CALLED_SOURCES, self::UNUSED_SOURCES), actual: $keys);

		$controller = (string) file_get_contents($this->root() . '/lib/Controller/ConfigurationController.php');
		foreach ($keys as $key) {
			$this->assertStringContainsString(
				needle: "'" . $key . "Location',",
				haystack: $controller,
				message: $key . ' is declared but ConfigurationController saves no location for it'
			);
		}
	}//end testTheKeysAreUniqueAndTheSavedSources()

	/**
	 * Every entry uses only schema fields with the schema's types, and a rising order.
	 *
	 * @return void
	 */
	public function testEveryEntryHasTheShapeIntegriqValidates(): void {
		$previousOrder = 0;
		foreach ($this->declaration()['connections'] as $connection) {
			$key = (string) $connection['key'];

			$this->assertSame(
				expected: [],
				actual: array_diff(array_keys($connection), array_keys(self::FIELD_TYPES)),
				message: $key . ' carries a field the schema does not allow'
			);
			foreach ($connection as $field => $value) {
				$this->assertSame(
					expected: self::FIELD_TYPES[$field],
					actual: $this->jsonType(value: $value),
					message: $key . '.' . $field . ' has the wrong type'
				);
			}

			$this->assertMatchesRegularExpression(pattern: '/^[a-z0-9]+(-[a-z0-9]+)*$/', string: $key);
			$this->assertNotSame(expected: '', actual: trim((string) ($connection['title'] ?? '')), message: $key . ' has no title');
			$this->assertGreaterThan(expected: $previousOrder, actual: $connection['order'], message: $key . ' breaks the page order');
			$previousOrder = $connection['order'];
		}
	}//end testEveryEntryHasTheShapeIntegriqValidates()

	/**
	 * No text a reader sees carries an em-dash (voice rule 8).
	 *
	 * @return void
	 */
	public function testNoTextCarriesAnEmDash(): void {
		$this->assertStringNotContainsString(needle: "\u{2014}", haystack: $this->raw());
		$this->assertStringNotContainsString(needle: '--', haystack: $this->raw());
	}//end testNoTextCarriesAnEmDash()

	/**
	 * No entry links to a settings section, because no section writes these keys.
	 *
	 * The admin page's only section, Data storage, saves `{objectType}_source`,
	 * `_register` and `_schema`. A link there would open a form where the
	 * admin cannot set a ZRC address. If a section that writes the keys ever
	 * exists, this test is where the anchor gets checked.
	 *
	 * @return void
	 */
	public function testNoEntryLinksToASectionThatCannotSetIt(): void {
		$settingsPage = (string) file_get_contents($this->root() . '/src/views/settings/Settings.vue');

		foreach ($this->declaration()['connections'] as $connection) {
			$this->assertArrayNotHasKey(key: 'settingsUrl', array: $connection, message: $connection['key']);
			$this->assertStringNotContainsString(
				needle: $connection['key'] . 'Location',
				haystack: $settingsPage,
				message: 'Settings.vue now writes ' . $connection['key'] . 'Location: link the row to that section'
			);
		}
	}//end testNoEntryLinksToASectionThatCannotSetIt()

	/**
	 * The called sources require their location, name it, and are usable.
	 *
	 * `CallService::getConfig()` builds the base URI from `{source}Location`
	 * and sends no auth header when `{source}AuthType` is empty, so the
	 * location is the one key a working call needs.
	 *
	 * @return void
	 */
	public function testTheCalledSourcesRequireTheirLocation(): void {
		$byKey       = $this->connectionsByKey();
		$callService = (string) file_get_contents($this->root() . '/lib/Service/CallService.php');

		$this->assertStringContainsString(needle: '"{$source}Location"', haystack: $callService);
		foreach (self::CALLED_SOURCES as $key) {
			$this->assertStringContainsString(needle: "source: '" . $key . "'", haystack: $this->controllerSources());
			$this->assertSame(expected: [$key . 'Location'], actual: $byKey[$key]['requiredConfig']);
			$this->assertStringContainsString(needle: $key . 'Location', haystack: $byKey[$key]['unconfiguredMessage']);
			$this->assertArrayNotHasKey(key: 'available', array: $byKey[$key]);
			$this->assertArrayNotHasKey(key: 'reportedOnly', array: $byKey[$key]);
		}
	}//end testTheCalledSourcesRequireTheirLocation()

	/**
	 * The unused sources are not available, say why, and nothing calls them.
	 *
	 * @return void
	 */
	public function testTheUnusedSourcesAreNotAvailableAndSayWhy(): void {
		$byKey   = $this->connectionsByKey();
		$callers = $this->controllerSources();

		foreach (self::UNUSED_SOURCES as $key) {
			$this->assertFalse(condition: $byKey[$key]['available'], message: $key);
			$this->assertMatchesRegularExpression(pattern: '/settings are kept/i', string: $byKey[$key]['unavailableMessage']);
			$this->assertMatchesRegularExpression(pattern: '/nothing in the app calls/i', string: $byKey[$key]['unavailableMessage']);
			$this->assertArrayNotHasKey(key: 'requiredConfig', array: $byKey[$key]);
			$this->assertStringNotContainsString(
				needle: "source: '" . $key . "'",
				haystack: $callers,
				message: $key . ' is called now: declare it with requiredConfig instead'
			);
		}
	}//end testTheUnusedSourcesAreNotAvailableAndSayWhy()

	/**
	 * The report service reports and refreshes exactly the called sources.
	 *
	 * A report for a key the file does not declare is refused by integriq, and
	 * a required key a save does not refresh leaves the row stale until
	 * integriq's hourly job.
	 *
	 * @return void
	 */
	public function testTheReportServiceCoversExactlyTheCalledSources(): void {
		$withRequiredConfig = array_keys(
			array_filter(
				$this->connectionsByKey(),
				static fn (array $connection): bool => isset($connection['requiredConfig']) === true
			)
		);

		$this->assertSame(expected: $withRequiredConfig, actual: array_keys(ConnectionReportService::REPORTED_SOURCES));
		$this->assertSame(expected: $withRequiredConfig, actual: array_keys(ConnectionReportService::REFRESH_KEYS));
		foreach ($withRequiredConfig as $key) {
			$this->assertSame(
				expected: [],
				actual: array_diff($this->connectionsByKey()[$key]['requiredConfig'], ConnectionReportService::REFRESH_KEYS[$key]),
				message: $key . ' has a required key a save does not refresh'
			);
		}
	}//end testTheReportServiceCoversExactlyTheCalledSources()

	/**
	 * Every PHP file under lib/Controller, concatenated.
	 *
	 * @return string
	 */
	private function controllerSources(): string {
		$sources = '';
		foreach (glob($this->root() . '/lib/Controller/*.php') as $file) {
			$sources .= (string) file_get_contents($file);
		}

		return $sources;
	}//end controllerSources()

	/**
	 * The JSON type name of a decoded value.
	 *
	 * @param mixed $value The decoded value.
	 *
	 * @return string
	 */
	private function jsonType(mixed $value): string {
		return match (true) {
			is_bool($value) => 'boolean',
			is_int($value) => 'integer',
			is_string($value) => 'string',
			is_array($value) => 'array',
			default => get_debug_type($value),
		};
	}//end jsonType()
}//end class
