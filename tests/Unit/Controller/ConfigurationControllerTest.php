<?php

/**
 * Wire-contract tests for the configuration save endpoint.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Controller
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Controller;

use OCA\ZaakAfhandelApp\Controller\ConfigurationController;
use OCA\ZaakAfhandelApp\Service\ConnectionReportService;
use OCP\AppFramework\Http;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Locks the wire contract of POST /api/configuration -> configuration#save.
 *
 * The configuration holds the ZGW service-account API keys (issue #267), so
 * this endpoint has two halves that fail in opposite directions and both are
 * asserted here:
 *
 *   - the WRITE half must accept only keys on WRITABLE_KEYS. An unfiltered
 *     upsert would let a caller set any app config value on the instance, and
 *     nothing in the response body would show it happened.
 *   - the RESPONSE half must redact credential keys to "***". The browser gets
 *     this body back; echoing the key it just posted is how a secret ends up in
 *     a screenshot, a HAR file, or a frontend store.
 *
 * The pair matters more than either alone: redaction must NOT be implemented by
 * masking before the write, or the endpoint would quietly store "***" as the
 * API key. So the test asserts the mask in the body AND the real value in the
 * store, from the same request.
 */
class ConfigurationControllerTest extends TestCase {

	/**
	 * The credential value posted — asserted present in the store and ABSENT
	 * from the response body.
	 *
	 * @var string
	 */
	private const SECRET = 'zrc-api-key-8f21';

	/**
	 * @var IAppConfig&MockObject
	 */
	private $config;

	/**
	 * Values the controller actually persisted, keyed `app/key`.
	 *
	 * @var array<string, string>
	 */
	private $written = [];

	protected function setUp(): void {
		$this->written = [];
		$this->config = $this->createMock(IAppConfig::class);

		$this->config->method('setValueString')->willReturnCallback(
			function (string $app, string $key, string $value): bool {
				$this->written[$app . '/' . $key] = $value;
				return true;
			}
		);

		$this->config->method('getValueString')->willReturnCallback(
			function (string $app, string $key, string $default = '') {
				return ($this->written[$app . '/' . $key] ?? $default);
			}
		);

	}//end setUp()

	/**
	 * Build the controller under test with the given POST body.
	 *
	 * @param array<string, mixed> $params The request parameters.
	 * @param bool $authenticated Whether IUserSession returns a user.
	 * @param ConnectionReportService|null $reports The connection reporter, or none.
	 *
	 * @return ConfigurationController The controller under test.
	 */
	private function makeController(array $params, bool $authenticated = true, ?ConnectionReportService $reports = null): ConfigurationController {
		$request = $this->createMock(IRequest::class);
		$request->method('getParams')->willReturn($params);

		$session = $this->createMock(IUserSession::class);
		$session->method('getUser')->willReturn($authenticated === true ? $this->createMock(IUser::class) : null);

		return new ConfigurationController('zaakafhandelapp', $this->config, $request, $session, $reports);
	}//end makeController()

	/**
	 * A save persists every writable key it was given, echoes the plain ones
	 * back, and returns 200.
	 *
	 * @return void
	 */
	public function testSavePersistsWritableKeysAndEchoesThePlainOnes(): void {
		$response = $this->makeController(
			[
				'zrcLocation' => 'https://zrc.example/api/v1',
				'organisationName' => 'Gemeente Voorbeeld',
			]
		)->save();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame('https://zrc.example/api/v1', $this->written['zaakafhandelapp/zrcLocation']);
		$this->assertSame('Gemeente Voorbeeld', $this->written['zaakafhandelapp/organisationName']);
		$this->assertSame('https://zrc.example/api/v1', $response->getData()['zrcLocation']);
		$this->assertSame('Gemeente Voorbeeld', $response->getData()['organisationName']);
	}//end testSavePersistsWritableKeysAndEchoesThePlainOnes()

	/**
	 * A credential key is stored verbatim but redacted in the response — the
	 * mask must never be what gets written.
	 *
	 * @return void
	 */
	public function testCredentialIsStoredVerbatimButRedactedInTheResponse(): void {
		$response = $this->makeController(['zrcKey' => self::SECRET])->save();

		$this->assertSame(self::SECRET, $this->written['zaakafhandelapp/zrcKey'], 'the real key must reach the store');
		$this->assertSame('***', $response->getData()['zrcKey'], 'the body must carry the mask');
		$this->assertStringNotContainsString(
			self::SECRET,
			(string)json_encode($response->getData()),
			'the response body must never echo the credential'
		);
	}//end testCredentialIsStoredVerbatimButRedactedInTheResponse()

	/**
	 * A key outside WRITABLE_KEYS is ignored: nothing is written for it and it
	 * does not appear in the response.
	 *
	 * The `_route` parameter is included because Nextcloud injects it into
	 * every request — it must be dropped by the same allow-list, not by a
	 * special case.
	 *
	 * @return void
	 */
	public function testKeysOutsideTheAllowListAreIgnored(): void {
		$response = $this->makeController(
			[
				'zrcLocation' => 'https://zrc.example/api/v1',
				'backgroundjobs' => 'cron',
				'installed' => 'no',
				'_route' => 'zaakafhandelapp.configuration.save',
			]
		)->save();

		$this->assertSame(['zaakafhandelapp/zrcLocation'], array_keys($this->written));
		$this->assertSame(['zrcLocation'], array_keys($response->getData()));
	}//end testKeysOutsideTheAllowListAreIgnored()

	/**
	 * A key the caller did not send is left alone — save() is a partial upsert,
	 * not a replace. A full-replace implementation would blank every other
	 * setting on any single-field save from the admin form.
	 *
	 * @return void
	 */
	public function testAbsentKeysAreNotWritten(): void {
		$this->makeController(['zrcLocation' => 'https://zrc.example/api/v1'])->save();

		$this->assertArrayNotHasKey('zaakafhandelapp/ztcLocation', $this->written);
		$this->assertArrayNotHasKey('zaakafhandelapp/zrcKey', $this->written);
	}//end testAbsentKeysAreNotWritten()

	/**
	 * A save hands the keys it wrote to the reporter, and nothing it ignored.
	 *
	 * Integriq only learns a ZRC address changed if it is asked. The keys are
	 * the WRITTEN ones: a key outside the allow-list was never saved, so it
	 * must not trigger a refresh either.
	 *
	 * @return void
	 *
	 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
	 */
	public function testASaveAsksIntegriqAboutTheKeysItWrote(): void {
		$reports = $this->createMock(originalClassName: ConnectionReportService::class);
		$reports->expects($this->once())
			->method('refreshFromSave')
			->with(['zrcLocation', 'zrcKey'])
			->willReturn(['zrc']);

		$response = $this->makeController(
			params: ['zrcLocation' => 'https://zrc.example/api/v1', 'zrcKey' => self::SECRET, 'installed' => 'no'],
			reports: $reports
		)->save();

		$this->assertSame(expected: Http::STATUS_OK, actual: $response->getStatus());
	}//end testASaveAsksIntegriqAboutTheKeysItWrote()

	/**
	 * The response is the same with and without the reporter.
	 *
	 * @return void
	 *
	 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
	 */
	public function testTheResponseIsTheSameWithAndWithoutTheReporter(): void {
		$params = ['zrcLocation' => 'https://zrc.example/api/v1', 'zrcKey' => self::SECRET];

		$with    = $this->makeController(params: $params, reports: $this->createMock(originalClassName: ConnectionReportService::class))->save();
		$without = $this->makeController(params: $params)->save();

		$this->assertSame(expected: $without->getData(), actual: $with->getData());
		$this->assertSame(expected: $without->getStatus(), actual: $with->getStatus());
	}//end testTheResponseIsTheSameWithAndWithoutTheReporter()

	/**
	 * An unauthenticated caller asks integriq about nothing.
	 *
	 * @return void
	 *
	 * @spec openspec/changes/adopt-connection-registry/specs/app-configuration/spec.md#requirement-req-zaa-conn-002-a-save-asks-integriq-to-look-again-and-a-zgw-call-reports-what-it-met
	 */
	public function testAnUnauthenticatedSaveAsksNothing(): void {
		$reports = $this->createMock(originalClassName: ConnectionReportService::class);
		$reports->expects($this->never())->method('refreshFromSave');

		$this->makeController(params: ['zrcLocation' => 'https://zrc.example/api/v1'], authenticated: false, reports: $reports)->save();
	}//end testAnUnauthenticatedSaveAsksNothing()

	/**
	 * An unauthenticated caller gets 401 and nothing is persisted.
	 *
	 * @return void
	 */
	public function testUnauthenticatedAnswers401AndPersistsNothing(): void {
		$response = $this->makeController(['zrcKey' => self::SECRET], false)->save();

		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus());
		$this->assertSame([], $this->written);
	}//end testUnauthenticatedAnswers401AndPersistsNothing()
}//end class
