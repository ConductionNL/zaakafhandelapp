<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\KlantContactSyncService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IUserSession;

/**
 * Controller for handling clients (klanten) operations.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class KlantenController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly IUserSession $userSession,
        private readonly KlantContactSyncService $contactSyncService,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Return (and serach) all objects
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-001
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Retrieve all request parameters
        $requestParams = $this->request->getParams();

        // Fetch catalog objects based on filters and order
        $data = $this->objectService->getResultArrayForRequest('klanten', $requestParams);

        // Return JSON response
        return new JSONResponse($data);
    }//end index()

    /**
     * Render no page.
     *
     * @param  string|null $getParameter Optional GET parameter
     * @return TemplateResponse The rendered template response
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-004
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $getParameter is an NC route param
     *   reserved for future SPA deep-linking; the PHP layer renders a shell template only.
     */
    public function page(?string $getParameter): TemplateResponse
    {
        try {
            // Create a new TemplateResponse for the index page
            $response = new TemplateResponse(
                $this->appName,
                'index',
                []
            );

            // Set up Content Security Policy
            $csp = new ContentSecurityPolicy();
            $csp->addAllowedConnectDomain('*');
            $response->setContentSecurityPolicy($csp);

            return $response;
        } catch (\Exception $e) {
            // Return an error template response if an exception occurs
            return new TemplateResponse(
                $this->appName,
                'error',
                ['error' => $e->getMessage()],
                '500'
            );
        }//end try
    }//end page()

    /**
     * Read a single object
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-001
     */
    public function show(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Fetch the catalog object by its ID
        $object = $this->objectService->getObject('klanten', $id);

        if ($object === null) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        // Return the catalog as a JSON response
        return new JSONResponse($object);
    }//end show()

    /**
     * Create an object. Admin-only: klanten are master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Get all parameters from the request
        $data = $this->request->getParams();

        // Remove the 'id' field if it exists, as we're creating a new object
        unset($data['id']);

        // Save the new catalog object
        $object = $this->objectService->saveObject('klanten', $data);

        // Push to the linked addressbook contact when the klant carries a
        // contactsUid; never fatal when Contacts is unavailable (REQ-003/004).
        $this->contactSyncService->pushKlant((array) $object);

        // Return the created object as a JSON response
        return new JSONResponse($object);
    }//end create()

    /**
     * Update an object. Admin-only: klanten are master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Get all parameters from the request
        $data = $this->request->getParams();

        // Pin the ID from the URL to prevent IDOR: body-supplied id must not override path id.
        $data['id'] = $id;

        // Strip server-managed fields that callers must not overwrite directly.
        unset($data['created'], $data['updated']);

        // Save the updated object
        $object = $this->objectService->saveObject('klanten', $data);

        // Keep the linked addressbook contact in sync; skipped + logged (never
        // fatal) when the klant is unlinked or Contacts is unavailable (REQ-003/004).
        $this->contactSyncService->pushKlant((array) $object);

        // Return the created object as a JSON response
        return new JSONResponse($object);
    }//end update()

    /**
     * Delete an object. Admin-only: klanten are master data.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-002
     */
    public function destroy(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // Delete the catalog object
        $result = $this->objectService->deleteObject('klanten', $id);

        // Return the result as a JSON response
        return new JSONResponse(['success' => $result], $result === true ? 200 : 404);
    }//end destroy()

    /**
     * Get zaken for a specific klant
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-003
     */
    public function getZaken(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $requestParams = ['klant' => $id];
        $zaken         = $this->objectService->getResultArrayForRequest('zaken', $requestParams);
        return new JSONResponse($zaken);
    }//end getZaken()

    /**
     * Get taken for a specific klant
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-003
     */
    public function getTaken(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $requestParams = ['klant' => $id];
        $taken         = $this->objectService->getResultArrayForRequest('taken', $requestParams);
        return new JSONResponse($taken);
    }//end getTaken()

    /**
     * Get berichten for a specific klant
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-003
     */
    public function getBerichten(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $requestParams = ['gebruikerID' => $id];
        $berichten     = $this->objectService->getResultArrayForRequest('berichten', $requestParams);
        return new JSONResponse($berichten);
    }//end getBerichten()

    /**
     * Get contactmomenten for a specific klant
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-003
     */
    public function getContactmomenten(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $requestParams   = ['klant' => $id];
        $contactmomenten = $this->objectService->getResultArrayForRequest('contactmomenten', $requestParams);
        return new JSONResponse($contactmomenten);
    }//end getContactmomenten()

    /**
     * Get audit trail for a specific klant
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-client-interaction/spec.md#REQ-004
     */
    public function getAuditTrail(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        // IDOR guard: verify the object exists and is accessible before returning its audit trail.
        $object = $this->objectService->getObject('klanten', $id);
        if ($object === null) {
            return new JSONResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
        }

        $auditTrail = $this->objectService->getAuditTrail('klanten', $id);
        return new JSONResponse($auditTrail);
    }//end getAuditTrail()

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
    public function contactsStatus(): JSONResponse
    {
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
    public function searchContacts(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $query = (string) ($this->request->getParam('query', ''));

        return new JSONResponse($this->contactSyncService->searchContacts($query));
    }//end searchContacts()

    /**
     * Import a Nextcloud contact as a klant (REQ-002).
     *
     * Admin-only by Nextcloud default (no @NoAdminRequired): klanten are master
     * data, mirroring create()/update(). Idempotent on contactsUid.
     *
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-002
     */
    public function importContact(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        if ($this->contactSyncService->isAvailable() === false) {
            return new JSONResponse(['error' => 'Nextcloud Contacts is not available'], Http::STATUS_SERVICE_UNAVAILABLE);
        }

        $uid = (string) ($this->request->getParam('uid', ''));
        if ($uid === '') {
            return new JSONResponse(['error' => 'A contact uid is required'], Http::STATUS_BAD_REQUEST);
        }

        $type = $this->request->getParam('type');
        if ($type !== null && in_array($type, ['persoon', 'organisatie'], true) === false) {
            return new JSONResponse(['error' => 'Invalid klant type'], Http::STATUS_BAD_REQUEST);
        }

        try {
            $klant = $this->contactSyncService->importContact($uid, $type);
        } catch (\RuntimeException $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
        }

        return new JSONResponse($klant);
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
    public function exportContact(string $id): JSONResponse
    {
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
            $klant = $this->contactSyncService->exportKlant($id, $addressBookKey);
        } catch (\RuntimeException $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
        }

        return new JSONResponse($klant);
    }//end exportContact()
}//end class
