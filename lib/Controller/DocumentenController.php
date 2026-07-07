<?php

namespace OCA\ZaakAfhandelApp\Controller;

use Exception;
use OCA\ZaakAfhandelApp\Service\CaseDocumentException;
use OCA\ZaakAfhandelApp\Service\CaseDocumentService;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\StreamResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

/**
 * ZGW DRC documenten controller.
 *
 * Geeft invulling aan https://vng-realisatie.github.io/gemma-zaken/standaard/documenten/
 *
 * Serves the DRC enkelvoudiginformatieobjecten collection on
 * /api/drc/enkelvoudiginformatieobjecten: ZGW-shaped metadata is stored in
 * OpenRegister while the binary content is a real file in Nextcloud Files
 * (preview/share/versioning from NC core), linked by file id. Replaces the
 * former not-implemented stub.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class DocumentenController extends Controller
{
    /**
     * The OpenRegister object type backing DRC informatieobjecten.
     *
     * @var string
     */
    private const OBJECT_TYPE = 'documenten';

    public function __construct(
        string $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly CaseDocumentService $caseDocumentService,
        private readonly IURLGenerator $urlGenerator,
        private readonly IUserSession $userSession,
        private readonly LoggerInterface $logger,
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
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-003
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
     * List enkelvoudiginformatieobjecten in ZGW shape.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function index(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $params = $this->request->getParams();
            unset($params['_route']);
            $objects = $this->objectService->getObjects(objectType: self::OBJECT_TYPE, filters: $params);
            $results = array_map(fn (array $o): array => $this->mapDocument($o), $objects);

            return new JSONResponse(['results' => array_values($results)]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }//end index()

    /**
     * Read a single enkelvoudiginformatieobject.
     *
     * @param string $id The document id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function show(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $object = (array) $this->objectService->getObject(self::OBJECT_TYPE, $id);

            return new JSONResponse($this->mapDocument($object));
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Document not found.'], Http::STATUS_NOT_FOUND);
        }
    }//end show()

    /**
     * Create an enkelvoudiginformatieobject: write the file, persist metadata.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function create(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $data = $this->request->getParams();
        unset($data['id'], $data['_route']);

        if (empty($data['titel']) === true || empty($data['bronorganisatie']) === true) {
            return new JSONResponse(
                ['error' => "Fields 'titel' and 'bronorganisatie' are required."],
                Http::STATUS_BAD_REQUEST
            );
        }

        if (isset($data['inhoud']) === false || is_string($data['inhoud']) === false || $data['inhoud'] === '') {
            return new JSONResponse(['error' => "Field 'inhoud' (base64 content) is required."], Http::STATUS_BAD_REQUEST);
        }

        $bestandsnaam = (string) ($data['bestandsnaam'] ?? $data['titel']);
        $zaakFolder   = (string) ($data['zaak'] ?? 'algemeen');

        try {
            $written = $this->caseDocumentService->writeDocument($zaakFolder, $bestandsnaam, $data['inhoud']);
        } catch (CaseDocumentException $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
        }

        unset($data['inhoud']);
        $data['fileId']         = $written['fileId'];
        $data['bestandsomvang'] = $written['bestandsomvang'];
        $data['bestandsnaam']   = $bestandsnaam;
        $data['versie']         = 1;

        try {
            $object = (array) $this->objectService->saveObject(self::OBJECT_TYPE, $data);

            return new JSONResponse($this->mapDocument($object), Http::STATUS_CREATED);
        } catch (Exception $e) {
            // Roll back the orphaned file so storage does not leak.
            $this->caseDocumentService->deleteDocument($written['fileId']);

            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }//end create()

    /**
     * Update an enkelvoudiginformatieobject; replacing inhoud bumps versie.
     *
     * @param string $id The document id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function update(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $existing = (array) $this->objectService->getObject(self::OBJECT_TYPE, $id);
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Document not found.'], Http::STATUS_NOT_FOUND);
        }

        $data = $this->request->getParams();
        unset($data['_route']);
        $data['id'] = $id;

        if (isset($data['inhoud']) === true && is_string($data['inhoud']) === true && $data['inhoud'] !== '') {
            $fileId = (int) ($existing['fileId'] ?? 0);
            if ($fileId === 0) {
                return new JSONResponse(['error' => 'Document has no backing file to replace.'], Http::STATUS_BAD_REQUEST);
            }

            try {
                $size = $this->caseDocumentService->replaceContent($fileId, $data['inhoud']);
            } catch (CaseDocumentException $e) {
                return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
            }

            unset($data['inhoud']);
            $data['bestandsomvang'] = $size;
            $data['versie']         = ((int) ($existing['versie'] ?? 1) + 1);
            $data['fileId']         = $fileId;
        }

        try {
            $object = (array) $this->objectService->saveObject(self::OBJECT_TYPE, $data);

            return new JSONResponse($this->mapDocument($object));
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }//end update()

    /**
     * Delete an enkelvoudiginformatieobject and its backing file.
     *
     * @param string $id The document id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function destroy(string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $existing = (array) $this->objectService->getObject(self::OBJECT_TYPE, $id);
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Document not found.'], Http::STATUS_NOT_FOUND);
        }

        $fileId = (int) ($existing['fileId'] ?? 0);
        if ($fileId !== 0 && $this->caseDocumentService->deleteDocument($fileId) === false) {
            $this->logger->warning(
                "Backing file $fileId for document $id was already gone; deleting metadata only.",
                ['app' => 'zaakafhandelapp']
            );
        }

        try {
            $this->objectService->deleteObject(self::OBJECT_TYPE, $id);

            return new JSONResponse([], Http::STATUS_NO_CONTENT);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }//end destroy()

    /**
     * Stream the stored bytes of an enkelvoudiginformatieobject.
     *
     * @param string $id The document id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse|StreamResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function download(string $id): JSONResponse | StreamResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $object = (array) $this->objectService->getObject(self::OBJECT_TYPE, $id);
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Document not found.'], Http::STATUS_NOT_FOUND);
        }

        $fileId = (int) ($object['fileId'] ?? 0);
        if ($fileId === 0) {
            return new JSONResponse(['error' => 'Document has no backing file.'], Http::STATUS_NOT_FOUND);
        }

        try {
            $read = $this->caseDocumentService->readStream($fileId);
        } catch (CaseDocumentException $e) {
            return new JSONResponse(['error' => 'Document content not found.'], Http::STATUS_NOT_FOUND);
        }

        $response = new StreamResponse($read['stream']);
        $response->addHeader('Content-Type', $read['mime']);
        $response->addHeader('Content-Disposition', 'attachment; filename="'.rawurlencode($read['name']).'"');

        return $response;
    }//end download()

    /**
     * Maps a stored document object onto the ZGW EnkelvoudigInformatieObject shape.
     *
     * Untracked ZGW fields are null, never fabricated; `inhoud` is the download URL.
     *
     * @param array $object The stored document.
     *
     * @return array The ZGW-shaped resource.
     */
    private function mapDocument(array $object): array
    {
        $uuid = ($object['id'] ?? $object['uuid'] ?? null);

        $inhoudUrl = null;
        if ($uuid !== null) {
            $inhoudUrl = $this->urlGenerator->getAbsoluteURL(
                '/index.php/apps/zaakafhandelapp/api/drc/enkelvoudiginformatieobjecten/'.(string) $uuid.'/download'
            );
        }

        return [
            'url'                         => $inhoudUrl === null ? null : str_replace('/download', '', $inhoudUrl),
            'uuid'                        => $uuid,
            'identificatie'               => ($object['identificatie'] ?? null),
            'bronorganisatie'             => ($object['bronorganisatie'] ?? null),
            'creatiedatum'                => ($object['creatiedatum'] ?? null),
            'titel'                       => ($object['titel'] ?? null),
            'auteur'                      => ($object['auteur'] ?? null),
            'status'                      => ($object['status'] ?? null),
            'taal'                        => ($object['taal'] ?? null),
            'formaat'                     => ($object['formaat'] ?? null),
            'bestandsnaam'                => ($object['bestandsnaam'] ?? null),
            'bestandsomvang'              => ($object['bestandsomvang'] ?? null),
            'versie'                      => ($object['versie'] ?? null),
            'beginRegistratie'            => ($object['beginRegistratie'] ?? null),
            'informatieobjecttype'        => ($object['informatieobjecttype'] ?? null),
            'vertrouwelijkheidaanduiding' => ($object['vertrouwelijkheidaanduiding'] ?? null),
            'lock'                        => null,
            'ondertekening'               => null,
            'integriteit'                 => null,
            'inhoud'                      => $inhoudUrl,
        ];
    }//end mapDocument()
}//end class
