<?php

/**
 * Unit tests for KlantContactSyncService.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Service
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Service;

use OCA\ZaakAfhandelApp\Service\KlantContactSyncService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\Contacts\IManager as IContactsManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Locks the klant ↔ addressbook contract: vCard round-trip mapping with bsn
 * exclusion, import idempotency on contactsUid, unknown-uid failure and the
 * graceful-degradation behaviour when Contacts is disabled.
 */
final class KlantContactSyncServiceTest extends TestCase {
	private IContactsManager $contacts;

	private ObjectService $objects;

	private LoggerInterface $logger;

	private KlantContactSyncService $service;

	protected function setUp(): void {
		$this->contacts = $this->createMock(IContactsManager::class);
		$this->objects = $this->createMock(ObjectService::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->service = new KlantContactSyncService($this->contacts, $this->objects, $this->logger);
	}//end setUp()

	public function testSearchReturnsEmptyWhenContactsDisabled(): void {
		$this->contacts->method('isEnabled')->willReturn(false);
		$this->contacts->expects($this->never())->method('search');

		$this->assertSame([], $this->service->searchContacts('Jansen'));
	}//end testSearchReturnsEmptyWhenContactsDisabled()

	public function testSearchFlagsAlreadyLinked(): void {
		$this->contacts->method('isEnabled')->willReturn(true);
		$this->contacts->method('search')->willReturn([
			['UID' => 'uid-1', 'FN' => 'Jan Jansen', 'EMAIL' => 'jan@example.com', 'addressbook-key' => 'ab1'],
			['UID' => 'uid-2', 'FN' => 'Piet Peters'],
		]);
		// One existing klant is linked to uid-1.
		$this->objects->method('getAllObjects')->willReturn([
			['id' => 'k1', 'contactsUid' => 'uid-1'],
			['id' => 'k2', 'contactsUid' => ''],
		]);

		$results = $this->service->searchContacts('Jan');

		$this->assertCount(2, $results);
		$this->assertTrue($results[0]['alreadyLinked']);
		$this->assertSame('Jan Jansen', $results[0]['name']);
		$this->assertFalse($results[1]['alreadyLinked']);
	}//end testSearchFlagsAlreadyLinked()

	public function testVCardToKlantMapsPersonFields(): void {
		$customer = $this->service->vCardToKlant([
			'N' => 'Jansen;Jan;;de;',
			'FN' => 'Jan de Jansen',
			'EMAIL' => 'jan@example.com',
			'TEL' => '0612345678',
			'ADR' => ';;Dorpsstraat 1;Utrecht;;3500AA;Nederland',
		]);

		$this->assertSame('persoon', $customer['type']);
		$this->assertSame('Jan', $customer['voornaam']);
		$this->assertSame('Jansen', $customer['achternaam']);
		$this->assertSame('de', $customer['tussenvoegsel']);
		$this->assertSame('jan@example.com', $customer['emailadres']);
		$this->assertSame('0612345678', $customer['telefoonnummer']);
		$this->assertSame('Dorpsstraat 1', $customer['straatnaam']);
		$this->assertSame('Utrecht', $customer['plaats']);
		$this->assertSame('3500AA', $customer['postcode']);
	}//end testVCardToKlantMapsPersonFields()

	public function testVCardToKlantTypesOrganisationFromOrg(): void {
		$customer = $this->service->vCardToKlant([
			'FN' => 'Acme BV',
			'ORG' => 'Acme BV',
		]);

		$this->assertSame('organisatie', $customer['type']);
		$this->assertSame('Acme BV', $customer['bedrijfsnaam']);
	}//end testVCardToKlantTypesOrganisationFromOrg()

	public function testKlantToVCardExcludesBsn(): void {
		$properties = $this->service->klantToVCard([
			'type' => 'persoon',
			'voornaam' => 'Jan',
			'achternaam' => 'Jansen',
			'tussenvoegsel' => 'de',
			'emailadres' => 'jan@example.com',
			'telefoonnummer' => '0612345678',
			'bsn' => '123456789',
		], 'uid-1');

		$this->assertSame('uid-1', $properties['UID']);
		$this->assertSame('Jan de Jansen', $properties['FN']);
		$this->assertSame('jan@example.com', $properties['EMAIL']);
		$this->assertArrayNotHasKey('bsn', $properties);
		// The bsn value must not leak into any property value either.
		$this->assertStringNotContainsString('123456789', json_encode($properties));
	}//end testKlantToVCardExcludesBsn()

	public function testImportCreatesNewKlantWithContactsUid(): void {
		$this->contacts->method('isEnabled')->willReturn(true);
		$this->contacts->method('search')->willReturn([
			['UID' => 'uid-1', 'FN' => 'Jan Jansen', 'EMAIL' => 'jan@example.com'],
		]);
		// No existing klant linked to uid-1.
		$this->objects->method('getObjects')->willReturn([]);

		$captured = null;
		$this->objects->method('saveObject')->willReturnCallback(function (string $type, array $obj) use (&$captured) {
			$captured = $obj;
			$obj['id'] = 'new-klant';
			return $obj;
		});

		$customer = $this->service->importContact('uid-1');

		$this->assertSame('uid-1', $captured['contactsUid']);
		$this->assertArrayNotHasKey('id', $captured);
		$this->assertSame('new-klant', $customer['id']);
	}//end testImportCreatesNewKlantWithContactsUid()

	public function testImportIsIdempotentOnContactsUid(): void {
		$this->contacts->method('isEnabled')->willReturn(true);
		$this->contacts->method('search')->willReturn([
			['UID' => 'uid-1', 'FN' => 'Jan Jansen', 'EMAIL' => 'new@example.com'],
		]);
		// An existing klant already carries uid-1.
		$this->objects->method('getObjects')->willReturn([
			['id' => 'existing-1', 'contactsUid' => 'uid-1', 'emailadres' => 'old@example.com'],
		]);

		$captured = null;
		$this->objects->method('saveObject')->willReturnCallback(function (string $type, array $obj) use (&$captured) {
			$captured = $obj;
			return $obj;
		});

		$customer = $this->service->importContact('uid-1');

		// Updates the existing klant (same id), never creates a duplicate.
		$this->assertSame('existing-1', $captured['id']);
		$this->assertSame('uid-1', $captured['contactsUid']);
		$this->assertSame('new@example.com', $captured['emailadres']);
		$this->assertSame('existing-1', $customer['id']);
	}//end testImportIsIdempotentOnContactsUid()

	public function testImportUnknownUidThrows(): void {
		$this->contacts->method('isEnabled')->willReturn(true);
		$this->contacts->method('search')->willReturn([]);

		$this->expectException(RuntimeException::class);
		$this->service->importContact('does-not-exist');
	}//end testImportUnknownUidThrows()

	public function testImportThrowsWhenContactsDisabled(): void {
		$this->contacts->method('isEnabled')->willReturn(false);

		$this->expectException(RuntimeException::class);
		$this->service->importContact('uid-1');
	}//end testImportThrowsWhenContactsDisabled()

	public function testPushKlantSkipsWhenUnlinked(): void {
		$this->contacts->expects($this->never())->method('createOrUpdate');

		$this->assertFalse($this->service->pushKlant(['id' => 'k1']));
	}//end testPushKlantSkipsWhenUnlinked()

	public function testPushKlantSkipsAndLogsWhenContactsDisabled(): void {
		$this->contacts->method('isEnabled')->willReturn(false);
		$this->contacts->expects($this->never())->method('createOrUpdate');
		$this->logger->expects($this->once())->method('info');

		$this->assertFalse($this->service->pushKlant(['id' => 'k1', 'contactsUid' => 'uid-1']));
	}//end testPushKlantSkipsAndLogsWhenContactsDisabled()

	public function testPushKlantUpdatesVCard(): void {
		$this->contacts->method('isEnabled')->willReturn(true);
		$this->contacts->method('search')->willReturn([
			['UID' => 'uid-1', 'addressbook-key' => 'ab1'],
		]);

		$captured = null;
		$this->contacts->expects($this->once())->method('createOrUpdate')
			->willReturnCallback(function (array $props, string $key) use (&$captured) {
				$captured = $props;
				return $props;
			});

		$result = $this->service->pushKlant([
			'id' => 'k1',
			'contactsUid' => 'uid-1',
			'type' => 'persoon',
			'voornaam' => 'Jan',
			'achternaam' => 'Jansen',
			'telefoonnummer' => '0699999999',
			'bsn' => '123456789',
		]);

		$this->assertTrue($result);
		$this->assertSame('uid-1', $captured['UID']);
		$this->assertSame('0699999999', $captured['TEL']);
		$this->assertStringNotContainsString('123456789', json_encode($captured));
	}//end testPushKlantUpdatesVCard()

	public function testIsAvailableReflectsManager(): void {
		$this->contacts->method('isEnabled')->willReturn(true);
		$this->assertTrue($this->service->isAvailable());
	}//end testIsAvailableReflectsManager()
}//end class
