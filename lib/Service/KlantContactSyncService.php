<?php

/**
 * Zaak Afhandel App — Klant ↔ Nextcloud addressbook sync service.
 *
 * Searches the Nextcloud addressbooks, imports contacts as klanten and pushes
 * linked klanten back to the addressbook, so klant master data stays a single
 * OpenRegister object (storage/RBAC/audit) while the contact-card projection
 * lives in the shared Nextcloud addressbook other apps (Mail/Talk/Calendar)
 * read. Modelled on pipelinq's ContactSyncService.
 *
 * @category Service
 * @package  OCA\ZaakAfhandelApp\Service
 *
 * @author    Conduction <info@conduction.nl>
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Service;

use OCP\Constants;
use OCP\Contacts\IManager as IContactsManager;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Service for searching, importing and exporting klanten against the
 * Nextcloud addressbook through OCP\Contacts\IManager.
 */
class KlantContactSyncService {
	/**
	 * Object type for klant records in OpenRegister.
	 *
	 * @var string
	 */
	private const KLANT_TYPE = 'klanten';

	/**
	 * Constructor.
	 *
	 * The mapper carries a default so the three collaborators that actually
	 * reach outside this process stay the constructor's story; it is a pure,
	 * dependency-free shape translator.
	 *
	 * @param IContactsManager $contactsManager The Nextcloud contacts manager.
	 * @param ObjectService $objectService The app object service (OpenRegister).
	 * @param LoggerInterface $logger The logger.
	 * @param KlantVCardMapper $mapper The klant <-> vCard shape mapper.
	 */
	public function __construct(
		private readonly IContactsManager $contactsManager,
		private readonly ObjectService $objectService,
		private readonly LoggerInterface $logger,
		private readonly KlantVCardMapper $mapper = new KlantVCardMapper(),
	) {
	}//end __construct()

	/**
	 * Whether the Nextcloud Contacts integration is available.
	 *
	 * @return boolean True when the Contacts manager is enabled.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-004
	 */
	public function isAvailable(): bool {
		return $this->contactsManager->isEnabled() === true;
	}//end isAvailable()

	/**
	 * List the user's writable addressbooks (key + display name).
	 *
	 * Used by the frontend to offer an export target and to confirm an export
	 * is possible. Returns an empty list when Contacts is disabled.
	 *
	 * @return array<int, array{key: string, displayName: string}> The writable books.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-003
	 */
	public function getWritableAddressBooks(): array {
		if ($this->isAvailable() === false) {
			return [];
		}

		$books = [];
		foreach ($this->contactsManager->getUserAddressBooks() as $book) {
			if (($book->getPermissions() & Constants::PERMISSION_CREATE) === 0) {
				continue;
			}

			$books[] = [
				'key' => $book->getKey(),
				'displayName' => $book->getDisplayName(),
			];
		}

		return $books;
	}//end getWritableAddressBooks()

	/**
	 * Search the user's accessible Nextcloud addressbooks (REQ-001).
	 *
	 * Results are decorated with an `alreadyLinked` flag that is true when a
	 * klant already stores the contact's uid as its `contactsUid`. Returns an
	 * empty list when Contacts is disabled.
	 *
	 * @param string $query The free-text search query.
	 *
	 * @return array<int, array<string, mixed>> The matching contacts.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-001
	 */
	public function searchContacts(string $query): array {
		if ($this->isAvailable() === false) {
			return [];
		}

		$results = $this->contactsManager->search(
			$query,
			['FN', 'EMAIL', 'TEL', 'ORG'],
			['limit' => 50]
		);

		$linkedUids = $this->getLinkedContactsUids();

		$contacts = [];
		foreach ($results as $result) {
			$uid = ($result['UID'] ?? null);
			if ($uid === null) {
				continue;
			}

			$contacts[] = [
				'uid' => $uid,
				'name' => $this->firstValue(value: ($result['FN'] ?? '')),
				'email' => $this->firstValue(value: ($result['EMAIL'] ?? '')),
				'phone' => $this->firstValue(value: ($result['TEL'] ?? '')),
				'org' => $this->firstValue(value: ($result['ORG'] ?? '')),
				'addressBookKey' => ($result['addressbook-key'] ?? ''),
				'alreadyLinked' => in_array($uid, $linkedUids, true),
			];
		}

		return $contacts;
	}//end searchContacts()

	/**
	 * Import a Nextcloud contact into a klant (REQ-002).
	 *
	 * Idempotent: when a klant already carries the contact's uid as its
	 * `contactsUid`, that klant is updated from the current vCard and returned
	 * instead of creating a duplicate. An unknown uid raises a RuntimeException.
	 *
	 * @param string $uid The contact UID to import.
	 * @param ?string $type Optional explicit klant type ('persoon'|'organisatie').
	 *
	 * @return array<string, mixed> The created or updated klant.
	 *
	 * @throws RuntimeException When Contacts is disabled or the uid is unknown.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-002
	 */
	public function importContact(string $uid, ?string $type = null): array {
		if ($this->isAvailable() === false) {
			throw new RuntimeException('Nextcloud Contacts is not available');
		}

		$contact = $this->findContactByUid(uid: $uid);
		if ($contact === null) {
			throw new RuntimeException('Contact not found in any accessible addressbook');
		}

		$klantFields = $this->vCardToKlant(contact: $contact, type: $type);

		$existing = $this->findKlantByContactsUid(uid: $uid);
		if ($existing !== null) {
			$merged = array_merge($existing, $klantFields);
			$merged['id'] = $existing['id'];
			$merged['contactsUid'] = $uid;

			return (array)$this->objectService->saveObject(self::KLANT_TYPE, $merged);
		}

		$klantFields['contactsUid'] = $uid;
		unset($klantFields['id']);

		return (array)$this->objectService->saveObject(self::KLANT_TYPE, $klantFields);
	}//end importContact()

	/**
	 * Push a linked klant to its addressbook vCard on save (REQ-003).
	 *
	 * Skips silently (logged, never fatal) when Contacts is disabled, the klant
	 * has no `contactsUid`, or the linked contact no longer exists. Never writes
	 * privacy-sensitive fields (bsn).
	 *
	 * @param array<string, mixed> $klant The klant to push.
	 *
	 * @return boolean True when the vCard was updated, false when skipped.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-003
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-004
	 */
	public function pushKlant(array $klant): bool {
		$uid = ($klant['contactsUid'] ?? '');
		if ($uid === '') {
			return false;
		}

		if ($this->isAvailable() === false) {
			$this->logger->info('Skipping addressbook push for klant; Contacts is disabled', ['uid' => $uid]);
			return false;
		}

		$contact = $this->findContactByUid(uid: $uid);
		if ($contact === null) {
			$this->logger->info('Skipping addressbook push; linked contact no longer exists', ['uid' => $uid]);
			return false;
		}

		$properties = $this->klantToVCard(klant: $klant, uid: $uid);
		$this->contactsManager->createOrUpdate($properties, ($contact['addressbook-key'] ?? ''));

		return true;
	}//end pushKlant()

	/**
	 * Export an unlinked klant to a writable addressbook (REQ-003).
	 *
	 * Creates a new vCard from the klant fields, stores the new uid as the
	 * klant's `contactsUid` and returns the updated klant.
	 *
	 * @param string $klantId The klant id to export.
	 * @param ?string $addressBookKey The target addressbook key, or null to use
	 *                                the first writable addressbook.
	 *
	 * @return array<string, mixed> The updated klant carrying its new contactsUid.
	 *
	 * @throws RuntimeException When Contacts is disabled, the klant is unknown,
	 *                          or no writable addressbook is available.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-003
	 */
	public function exportKlant(string $klantId, ?string $addressBookKey = null): array {
		if ($this->isAvailable() === false) {
			throw new RuntimeException('Nextcloud Contacts is not available');
		}

		if ($addressBookKey === null || $addressBookKey === '') {
			$writable = $this->getWritableAddressBooks();
			if ($writable === []) {
				throw new RuntimeException('No writable addressbook available');
			}

			$addressBookKey = $writable[0]['key'];
		}

		$klant = $this->objectService->getObject(self::KLANT_TYPE, $klantId);
		if ($klant === null) {
			throw new RuntimeException('Klant not found');
		}

		$klant = (array)$klant;

		$properties = $this->klantToVCard(klant: $klant, uid: null);
		$created = $this->contactsManager->createOrUpdate($properties, $addressBookKey);

		$newUid = ($created['UID'] ?? ($properties['UID'] ?? ''));
		$klant['contactsUid'] = $newUid;

		return (array)$this->objectService->saveObject(self::KLANT_TYPE, $klant);
	}//end exportKlant()

	/**
	 * Map a Nextcloud vCard result to klant fields (REQ-002).
	 *
	 * @param array<string, mixed> $contact The vCard key-value result.
	 * @param ?string $type Optional explicit klant type.
	 *
	 * @return array<string, mixed> The klant field set.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-002
	 */
	public function vCardToKlant(array $contact, ?string $type = null): array {
		return $this->mapper->vCardToKlant($contact, $type);
	}//end vCardToKlant()

	/**
	 * Map a klant to vCard properties (REQ-003).
	 *
	 * Privacy-sensitive fields (bsn) are never emitted. When $uid is provided
	 * the property set carries it so an update replaces the existing card.
	 *
	 * @param array<string, mixed> $klant The klant.
	 * @param ?string $uid The existing contact uid, or null for a new card.
	 *
	 * @return array<string, mixed> The vCard property key-value set.
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-003
	 */
	public function klantToVCard(array $klant, ?string $uid): array {
		return $this->mapper->klantToVCard($klant, $uid);
	}//end klantToVCard()

	/**
	 * Collect all contactsUid values currently linked to a klant.
	 *
	 * @return array<int, string> The set of linked contact uids.
	 */
	private function getLinkedContactsUids(): array {
		$klanten = $this->objectService->getAllObjects(self::KLANT_TYPE);

		$uids = [];
		foreach ($klanten as $klant) {
			$klant = (array)$klant;
			$uid = ($klant['contactsUid'] ?? '');
			if ($uid !== '') {
				$uids[] = $uid;
			}
		}

		return $uids;
	}//end getLinkedContactsUids()

	/**
	 * Find the klant linked to a given contact uid, if any.
	 *
	 * @param string $uid The contact uid.
	 *
	 * @return ?array<string, mixed> The linked klant or null.
	 */
	private function findKlantByContactsUid(string $uid): ?array {
		$matches = $this->objectService->getObjects(
			self::KLANT_TYPE,
			null,
			null,
			['contactsUid' => $uid]
		);

		foreach ($matches as $klant) {
			$klant = (array)$klant;
			if (($klant['contactsUid'] ?? '') === $uid) {
				return $klant;
			}
		}

		return null;
	}//end findKlantByContactsUid()

	/**
	 * Find a Nextcloud contact by its exact uid.
	 *
	 * @param string $uid The contact uid.
	 *
	 * @return ?array<string, mixed> The contact or null when not found.
	 */
	private function findContactByUid(string $uid): ?array {
		$results = $this->contactsManager->search($uid, ['UID'], ['limit' => 5]);

		foreach ($results as $result) {
			if (($result['UID'] ?? '') === $uid) {
				return $result;
			}
		}

		return null;
	}//end findContactByUid()

	/**
	 * Extract the first scalar value from a vCard property that may be an
	 * array (multi-valued / typed) or a string.
	 *
	 * @param mixed $value The raw property value.
	 *
	 * @return string The first scalar value as a string.
	 */
	private function firstValue(mixed $value): string {
		return $this->mapper->firstValue($value);
	}//end firstValue()
}//end class
