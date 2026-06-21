<?php

namespace OCA\ZaakAfhandelApp\Controller;

use Exception;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCA\ZaakAfhandelApp\Service\ZGWRegistryService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

/**
 * ZGW ZRC zaakbesluiten controller.
 *
 * Manages ZGW ZaakBesluit relation resources on
 * /api/zrc/zaken/{zaak_uuid}/besluiten, backed by OpenRegister zaakbesluit
 * objects in the BRC register. The ZaakBesluit resource is the {url, uuid,
 * zaak, besluit} mirror of the BRC besluit↔zaak relation; the relation is
 * scoped to the routed zaak so one case's besluiten can never be read or
 * mutated through another case's path (IDOR-safe, ADR-005). Replaces the
 * former not-implemented stub.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ZaakBesluitenController extends Controller
{
    /**
     * The OpenRegister object type backing zaakbesluit relations.
     *
     * @var string
     */
    private const OBJECT_TYPE = 'zaakbesluit';

    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly ZGWRegistryService $registryService,
        private readonly IURLGenerator $urlGenerator,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * This returns the template of the main app's page.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function page(): TemplateResponse
    {
        return new TemplateResponse(
            'zaakafhandelapp',
            'index',
            []
        );
    }//end page()

    /**
     * List the zaakbesluit relations bound to the routed zaak.
     *
     * @param string $zaakUuid The zaak the relations belong to (route).
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-006
     */
    public function index(string $zaakUuid): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $objects = $this->objectService->getObjects(
                objectType: self::OBJECT_TYPE,
                filters: ['zaak' => $zaakUuid]
            );

            $results = array_map(fn (array $object): array => $this->mapZaakBesluit($object), $objects);

            return new JSONResponse(['results' => array_values($results)]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }//end index()

    /**
     * Read a single zaakbesluit relation scoped to the routed zaak.
     *
     * @param string $zaakUuid The zaak the relation must belong to (route).
     * @param string $id       The zaakbesluit id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-006
     */
    public function show(string $zaakUuid, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $object = $this->requireZaakBesluitOnZaak(id: $id, zaakUuid: $zaakUuid);

            return new JSONResponse($this->mapZaakBesluit($object));
        } catch (ZaakBesluitNotFoundException $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Zaakbesluit not found.'], Http::STATUS_NOT_FOUND);
        }
    }//end show()

    /**
     * Create a zaakbesluit relation on the routed zaak.
     *
     * @param string $zaakUuid The zaak the relation is created on (route).
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-006
     */
    public function create(string $zaakUuid): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $data = $this->request->getParams();
        unset($data['id'], $data['_route']);

        if ($this->requireZaakExists(zaakUuid: $zaakUuid) === false) {
            return new JSONResponse(['error' => "Zaak '$zaakUuid' does not exist."], Http::STATUS_NOT_FOUND);
        }

        if ($this->requireBesluitResolves(besluit: ($data['besluit'] ?? null)) === false) {
            return new JSONResponse(
                ['error' => "The 'besluit' reference is missing or does not resolve."],
                Http::STATUS_BAD_REQUEST
            );
        }

        $data['zaak'] = $zaakUuid;

        try {
            $object = (array) $this->objectService->saveObject(self::OBJECT_TYPE, $data);

            return new JSONResponse($this->mapZaakBesluit($object), Http::STATUS_CREATED);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }//end create()

    /**
     * Update a zaakbesluit relation on the routed zaak (only besluit is mutable).
     *
     * @param string $zaakUuid The zaak the relation must belong to (route).
     * @param string $id       The zaakbesluit id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-006
     */
    public function update(string $zaakUuid, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $data = $this->request->getParams();

        if (isset($data['besluit']) === true && $this->requireBesluitResolves(besluit: $data['besluit']) === false) {
            return new JSONResponse(
                ['error' => "The 'besluit' reference does not resolve."],
                Http::STATUS_BAD_REQUEST
            );
        }

        try {
            $this->requireZaakBesluitOnZaak(id: $id, zaakUuid: $zaakUuid);

            // Only besluit may change; zaak stays the routed value.
            $data['id']   = $id;
            $data['zaak'] = $zaakUuid;
            unset($data['_route']);

            $object = (array) $this->objectService->saveObject(self::OBJECT_TYPE, $data);

            return new JSONResponse($this->mapZaakBesluit($object));
        } catch (ZaakBesluitNotFoundException $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Zaakbesluit not found.'], Http::STATUS_NOT_FOUND);
        }
    }//end update()

    /**
     * Delete a zaakbesluit relation only — never the referenced besluit.
     *
     * @param string $zaakUuid The zaak the relation must belong to (route).
     * @param string $id       The zaakbesluit id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-006
     */
    public function destroy(string $zaakUuid, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $this->requireZaakBesluitOnZaak(id: $id, zaakUuid: $zaakUuid);

            $this->objectService->deleteObject(self::OBJECT_TYPE, $id);

            return new JSONResponse([], Http::STATUS_NO_CONTENT);
        } catch (ZaakBesluitNotFoundException $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Zaakbesluit not found.'], Http::STATUS_NOT_FOUND);
        }
    }//end destroy()

    /**
     * Maps a stored zaakbesluit object onto the ZGW ZaakBesluit shape.
     *
     * @param array $object The stored zaakbesluit.
     *
     * @return array{url: string, uuid: (string|null), zaak: mixed, besluit: mixed}
     */
    private function mapZaakBesluit(array $object): array
    {
        $uuid = ($object['id'] ?? $object['uuid'] ?? null);

        return [
            'url'     => $this->urlGenerator->getAbsoluteURL(
                '/index.php/apps/zaakafhandelapp/api/zrc/zaken/'
                .($this->resolveReference($object['zaak'] ?? '')).'/besluiten/'.(string) $uuid
            ),
            'uuid'    => $uuid,
            'zaak'    => ($object['zaak'] ?? null),
            'besluit' => ($object['besluit'] ?? null),
        ];
    }//end mapZaakBesluit()

    /**
     * Loads a zaakbesluit and enforces that it belongs to the routed zaak.
     *
     * The per-object IDOR guard: a relation of another case is never disclosed
     * or mutated through this case's path (404 instead).
     *
     * @param string $id       The zaakbesluit id.
     * @param string $zaakUuid The routed zaak.
     *
     * @return array The verified zaakbesluit.
     *
     * @throws ZaakBesluitNotFoundException When absent or bound to another zaak.
     */
    private function requireZaakBesluitOnZaak(string $id, string $zaakUuid): array
    {
        $object = (array) $this->objectService->getObject(self::OBJECT_TYPE, $id);

        if ($this->resolveReference($object['zaak'] ?? '') !== $this->resolveReference($zaakUuid)) {
            throw new ZaakBesluitNotFoundException('Zaakbesluit not found for this zaak.');
        }

        return $object;
    }//end requireZaakBesluitOnZaak()

    /**
     * Validates that the routed zaak exists in OpenRegister.
     *
     * @param string $zaakUuid The routed zaak.
     *
     * @return boolean True when the zaak resolves.
     */
    private function requireZaakExists(string $zaakUuid): bool
    {
        try {
            $zaak = (array) $this->objectService->getObject('zaken', $zaakUuid);

            return empty($zaak) === false;
        } catch (Exception $e) {
            return false;
        }
    }//end requireZaakExists()

    /**
     * Validates that a besluit reference resolves.
     *
     * A local uuid is read from OpenRegister; an absolute external URL is
     * accepted verbatim (ZGW allows remote BRC references).
     *
     * @param mixed $besluit The besluit reference (uuid or absolute URL).
     *
     * @return boolean True when the reference is valid.
     */
    private function requireBesluitResolves(mixed $besluit): bool
    {
        if (is_string($besluit) === false || $besluit === '') {
            return false;
        }

        if (filter_var($besluit, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        try {
            $object = (array) $this->objectService->getObject('besluiten', $besluit);

            return empty($object) === false;
        } catch (Exception $e) {
            return false;
        }
    }//end requireBesluitResolves()

    /**
     * Resolves a reference (uuid or URL) to its last path segment.
     *
     * @param mixed $reference The reference.
     *
     * @return string The trailing id.
     */
    private function resolveReference(mixed $reference): string
    {
        if (is_array($reference) === true) {
            $reference = ($reference['id'] ?? $reference['uuid'] ?? '');
        }

        if (is_string($reference) === false) {
            return '';
        }

        $parts = explode('/', rtrim($reference, '/'));

        return (string) end($parts);
    }//end resolveReference()
}//end class
