<?php

/**
 * Wire-contract tests for the klant <-> addressbook endpoints.
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

use OCA\ZaakAfhandelApp\Controller\KlantContactsController;
use OCA\ZaakAfhandelApp\Service\KlantContactSyncService;
use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Locks the wire contract of two addressbook endpoints:
 *
 *   GET  /api/klanten/contacts/status    -> klantContacts#contactsStatus
 *   POST /api/klanten/{id}/contacts/export -> klantContacts#exportContact
 *
 * contactsStatus() is a capability probe: the frontend hides the import/export
 * entry points when it reports false. Its whole contract is the boolean under
 * `available`, and it has to be a real boolean — a truthy string or a missing
 * key both render as "Contacts is there" and then the buttons 500.
 *
 * exportContact() writes into the user's addressbook, so its degraded paths are
 * the interesting ones. When Contacts is disabled it must answer 503 WITHOUT
 * calling the service: KlantContactSyncService::exportKlant() reaches into the
 * addressbook API that is not there, so "check first" is the difference between
 * a clean 503 and a 500 out of a missing app. A caller-supplied klant id that
 * fails must come back as a 400 carrying the service's message, not as an
 * unhandled RuntimeException.
 */
class KlantContactsControllerTest extends TestCase {

	/**
	 * The klant id used as the route parameter.
	 *
	 * @var string
	 */
	private const KLANT_ID = 'klant-42';

	/**
	 * @var KlantContactSyncService&MockObject
	 */
	private $contactSyncService;

	protected function setUp(): void {
		$this->contactSyncService = $this->createMock(KlantContactSyncService::class);

	}//end setUp()

	/**
	 * Build the controller under test.
	 *
	 * @param array<string, mixed> $params Request parameters exposed via getParam().
	 * @param bool $authenticated Whether IUserSession returns a user.
	 *
	 * @return KlantContactsController The controller under test.
	 */
	private function makeController(array $params = [], bool $authenticated = true): KlantContactsController {
		$request = $this->createMock(IRequest::class);
		$request->method('getParam')->willReturnCallback(
			function (string $key, $default = null) use ($params) {
				return ($params[$key] ?? $default);
			}
		);

		$session = $this->createMock(IUserSession::class);
		$session->method('getUser')->willReturn($authenticated === true ? $this->createMock(IUser::class) : null);

		return new KlantContactsController('zaakafhandelapp', $request, $session, $this->contactSyncService);
	}//end makeController()

	/**
	 * The probe reports the integration as available, as a real boolean true.
	 *
	 * @return void
	 */
	public function testContactsStatusReportsAvailable(): void {
		$this->contactSyncService->method('isAvailable')->willReturn(true);

		$response = $this->makeController()->contactsStatus();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['available' => true], $response->getData());
	}//end testContactsStatusReportsAvailable()

	/**
	 * A disabled Contacts app is reported as available:false with 200 — the
	 * probe itself must not fail, or the frontend cannot tell "off" from
	 * "broken".
	 *
	 * @return void
	 */
	public function testContactsStatusReportsUnavailableWithoutFailing(): void {
		$this->contactSyncService->method('isAvailable')->willReturn(false);

		$response = $this->makeController()->contactsStatus();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame(['available' => false], $response->getData());
	}//end testContactsStatusReportsUnavailableWithoutFailing()

	/**
	 * An unauthenticated probe answers 401 and never consults the service.
	 *
	 * @return void
	 */
	public function testContactsStatusUnauthenticatedAnswers401(): void {
		$this->contactSyncService->expects($this->never())->method('isAvailable');

		$this->assertSame(
			Http::STATUS_UNAUTHORIZED,
			$this->makeController([], false)->contactsStatus()->getStatus()
		);
	}//end testContactsStatusUnauthenticatedAnswers401()

	/**
	 * A successful export returns the klant the service produced, with 200, and
	 * passes through both the ROUTE id and the requested addressbook.
	 *
	 * @return void
	 */
	public function testExportPassesTheRouteIdAndAddressbookThrough(): void {
		$customer = [
			'id' => self::KLANT_ID,
			'contactsUid' => 'vcard-uid-1',
		];

		$this->contactSyncService->method('isAvailable')->willReturn(true);
		$this->contactSyncService->expects($this->once())
			->method('exportKlant')
			->with(self::KLANT_ID, 'contacts:personal')
			->willReturn($customer);

		$response = $this->makeController(['addressBookKey' => 'contacts:personal'])->exportContact(self::KLANT_ID);

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame($customer, $response->getData());
	}//end testExportPassesTheRouteIdAndAddressbookThrough()

	/**
	 * The addressbook key is optional: when it is absent the service is called
	 * with null so it can fall back to the first writable addressbook. It must
	 * not be coerced into an empty string, which names no addressbook at all.
	 *
	 * @return void
	 */
	public function testExportPassesNullWhenNoAddressbookWasRequested(): void {
		$this->contactSyncService->method('isAvailable')->willReturn(true);
		$this->contactSyncService->expects($this->once())
			->method('exportKlant')
			->with(self::KLANT_ID, null)
			->willReturn(['id' => self::KLANT_ID]);

		$this->assertSame(
			Http::STATUS_OK,
			$this->makeController()->exportContact(self::KLANT_ID)->getStatus()
		);
	}//end testExportPassesNullWhenNoAddressbookWasRequested()

	/**
	 * With Contacts disabled the export answers 503 and the service is never
	 * reached.
	 *
	 * @return void
	 */
	public function testExportAnswers503WhenContactsIsUnavailable(): void {
		$this->contactSyncService->method('isAvailable')->willReturn(false);
		$this->contactSyncService->expects($this->never())->method('exportKlant');

		$response = $this->makeController()->exportContact(self::KLANT_ID);

		$this->assertSame(Http::STATUS_SERVICE_UNAVAILABLE, $response->getStatus());
		$this->assertArrayHasKey('error', $response->getData());
	}//end testExportAnswers503WhenContactsIsUnavailable()

	/**
	 * A service-level failure becomes a 400 carrying the reason, not an
	 * unhandled exception.
	 *
	 * @return void
	 */
	public function testExportTranslatesAServiceFailureInto400(): void {
		$this->contactSyncService->method('isAvailable')->willReturn(true);
		$this->contactSyncService->method('exportKlant')
			->willThrowException(new RuntimeException('No writable addressbook'));

		$response = $this->makeController()->exportContact(self::KLANT_ID);

		$this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
		$this->assertSame('No writable addressbook', $response->getData()['error']);
	}//end testExportTranslatesAServiceFailureInto400()

	/**
	 * An unauthenticated export answers 401 and writes nothing.
	 *
	 * @return void
	 */
	public function testExportUnauthenticatedAnswers401AndWritesNothing(): void {
		$this->contactSyncService->expects($this->never())->method('exportKlant');

		$this->assertSame(
			Http::STATUS_UNAUTHORIZED,
			$this->makeController([], false)->exportContact(self::KLANT_ID)->getStatus()
		);
	}//end testExportUnauthenticatedAnswers401AndWritesNothing()
}//end class
