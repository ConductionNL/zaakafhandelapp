<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\KlantContactSyncService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;
use RuntimeException;

/**
 * Controller for the klant <-> Nextcloud addressbook integration.
 *
 * Split out of KlantenController, which had grown to cover both klant CRUD and
 * the whole addressbook-sync surface. These endpoints already lived under their
 * own /api/klanten/contacts/* URL group and are backed by a different service
 * (KlantContactSyncService rather than ObjectService), so they are a separate
 * concern; the URLs are unchanged, only the route names moved.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class KlantContactsController extends Controller {
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IUserSession $userSession,
		private readonly KlantContactSyncService $contactSyncService,
	) {
		parent::__construct($appName, $request);
	}//end __construct()

	/**
	 * Report whether the Nextcloud Contacts integration is available.
	 *
	 * Lets the frontend hide the import/export entry points when Contacts is
	 * disabled (REQ-004 / ui-client-views REQ-006). Read-only; any authenticated
	 * user may call it.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-004
	 */
	public function contactsStatus(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		return new JSONResponse(['available' => $this->contactSyncService->isAvailable()]);
	}//end contactsStatus()

	/**
	 * Search the Nextcloud addressbooks for contacts (REQ-001).
	 *
	 * Returns matching contacts decorated with an `alreadyLinked` flag. Empty
	 * result set when Contacts is disabled. Read-only over the user's own
	 * addressbooks — any authenticated user may search.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-001
	 */
	public function searchContacts(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		$query = (string)($this->request->getParam('query', ''));

		return new JSONResponse($this->contactSyncService->searchContacts($query));
	}//end searchContacts()

	/**
	 * Import a Nextcloud contact as a klant (REQ-002).
	 *
	 * Admin-only by Nextcloud default (no @NoAdminRequired): klanten are master
	 * data, mirroring KlantenController::create()/update(). Idempotent on
	 * contactsUid.
	 *
	 * @NoCSRFRequired
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-002
	 */
	public function importContact(): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		if ($this->contactSyncService->isAvailable() === false) {
			return new JSONResponse(['error' => 'Nextcloud Contacts is not available'], Http::STATUS_SERVICE_UNAVAILABLE);
		}

		$uid = (string)($this->request->getParam('uid', ''));
		if ($uid === '') {
			return new JSONResponse(['error' => 'A contact uid is required'], Http::STATUS_BAD_REQUEST);
		}

		$type = $this->request->getParam('type');
		if ($type !== null && in_array($type, ['persoon', 'organisatie'], true) === false) {
			return new JSONResponse(['error' => 'Invalid klant type'], Http::STATUS_BAD_REQUEST);
		}

		try {
			$customer = $this->contactSyncService->importContact($uid, $type);
		} catch (RuntimeException $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}

		return new JSONResponse($customer);
	}//end importContact()

	/**
	 * Export a klant to the addressbook (REQ-003).
	 *
	 * Admin-only by Nextcloud default (no @NoAdminRequired): klanten are master
	 * data. The klant id is pinned from the route — no IDOR.
	 *
	 * @NoCSRFRequired
	 *
	 * @param string $id The klant id to export.
	 *
	 * @return JSONResponse
	 *
	 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-003
	 */
	public function exportContact(string $id): JSONResponse {
		if ($this->userSession->getUser() === null) {
			return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
		}

		if ($this->contactSyncService->isAvailable() === false) {
			return new JSONResponse(['error' => 'Nextcloud Contacts is not available'], Http::STATUS_SERVICE_UNAVAILABLE);
		}

		// The addressbook key is optional; the service falls back to the first
		// writable addressbook when none is supplied.
		$addressBookKey = $this->request->getParam('addressBookKey');

		try {
			$customer = $this->contactSyncService->exportKlant($id, $addressBookKey);
		} catch (RuntimeException $e) {
			return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}

		return new JSONResponse($customer);
	}//end exportContact()
}//end class
