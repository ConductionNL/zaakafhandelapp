<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IUserSession;

/**
 * Stores DRC enkelvoudiginformatieobject binary content as real Nextcloud
 * files under a per-zaak case-documents folder, so NC core provides preview,
 * sharing and versioning for free. Metadata lives in OpenRegister; this
 * service owns only the file side, isolating the storage decision behind one
 * class.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class CaseDocumentService
{
    /**
     * The root folder name (under the acting user's files) for case documents.
     *
     * @var string
     */
    private const ROOT_FOLDER = 'Zaakdocumenten';

    public function __construct(
        private readonly IRootFolder $rootFolder,
        private readonly IUserSession $userSession,
    ) {
    }//end __construct()

    /**
     * Writes decoded base64 content to a file under the per-zaak folder.
     *
     * @param string $zaak         The zaak identification/uuid (folder name).
     * @param string $bestandsnaam The file name.
     * @param string $base64Inhoud The base64-encoded content.
     *
     * @return array{fileId: int, bestandsomvang: int} The stored file id and size.
     *
     * @throws CaseDocumentException When decoding fails or the file cannot be written.
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function writeDocument(string $zaak, string $bestandsnaam, string $base64Inhoud): array
    {
        $content = $this->decode($base64Inhoud);
        $folder  = $this->resolveZaakFolder($zaak);
        $name    = $this->sanitiseName($bestandsnaam);

        try {
            if ($folder->nodeExists($name) === true) {
                $node = $folder->get($name);
                if ($node instanceof File === true) {
                    $node->putContent($content);
                    $file = $node;
                } else {
                    throw new CaseDocumentException("Path '$name' is not a file.");
                }
            } else {
                $file = $folder->newFile($name, $content);
            }
        } catch (NotFoundException $e) {
            throw new CaseDocumentException('Could not write the document file: '.$e->getMessage());
        }

        return [
            'fileId'         => $file->getId(),
            'bestandsomvang' => $file->getSize(),
        ];
    }//end writeDocument()

    /**
     * Replaces the content of an existing file (NC versioning retains old bytes).
     *
     * @param integer $fileId       The Nextcloud file id.
     * @param string  $base64Inhoud The new base64-encoded content.
     *
     * @return integer The new file size.
     *
     * @throws CaseDocumentException When the file is missing or cannot be written.
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function replaceContent(int $fileId, string $base64Inhoud): int
    {
        $content = $this->decode($base64Inhoud);
        $file    = $this->requireFile($fileId);
        $file->putContent($content);

        return $file->getSize();
    }//end replaceContent()

    /**
     * Opens a read stream for the stored file.
     *
     * @param integer $fileId The Nextcloud file id.
     *
     * @return array{stream: resource, mime: string, name: string} The stream and metadata.
     *
     * @throws CaseDocumentException When the file is missing.
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function readStream(int $fileId): array
    {
        $file   = $this->requireFile($fileId);
        $stream = $file->fopen('r');

        if (is_resource($stream) === false) {
            throw new CaseDocumentException('Could not open the document file for reading.');
        }

        return [
            'stream' => $stream,
            'mime'   => $file->getMimeType(),
            'name'   => $file->getName(),
        ];
    }//end readStream()

    /**
     * Deletes the backing file. A missing file is treated as already deleted.
     *
     * @param integer $fileId The Nextcloud file id.
     *
     * @return boolean True when a file was deleted, false when it was already gone.
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-004
     */
    public function deleteDocument(int $fileId): bool
    {
        try {
            $this->requireFile($fileId)->delete();

            return true;
        } catch (CaseDocumentException $e) {
            return false;
        }
    }//end deleteDocument()

    /**
     * Resolves (creating if needed) the per-zaak folder under the user's files.
     *
     * @param string $zaak The zaak identification/uuid.
     *
     * @return Folder The per-zaak folder.
     *
     * @throws CaseDocumentException When no user is logged in or the folder cannot be created.
     */
    private function resolveZaakFolder(string $zaak): Folder
    {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new CaseDocumentException('No authenticated user to store the document for.');
        }

        $userFolder = $this->rootFolder->getUserFolder($user->getUID());
        $path       = self::ROOT_FOLDER.'/'.$this->sanitiseName($zaak);

        if ($userFolder->nodeExists($path) === true) {
            $node = $userFolder->get($path);
            if ($node instanceof Folder === true) {
                return $node;
            }

            throw new CaseDocumentException("Path '$path' exists but is not a folder.");
        }

        return $userFolder->newFolder($path);
    }//end resolveZaakFolder()

    /**
     * Loads a File node by id or throws.
     *
     * @param integer $fileId The Nextcloud file id.
     *
     * @return File The file node.
     *
     * @throws CaseDocumentException When no file with that id is reachable.
     */
    private function requireFile(int $fileId): File
    {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new CaseDocumentException('No authenticated user to read the document for.');
        }

        $userFolder = $this->rootFolder->getUserFolder($user->getUID());
        $nodes      = $userFolder->getById($fileId);

        foreach ($nodes as $node) {
            if ($node instanceof File === true) {
                return $node;
            }
        }

        throw new CaseDocumentException("No document file found for id $fileId.");
    }//end requireFile()

    /**
     * Decodes base64 content, rejecting invalid input.
     *
     * @param string $base64 The base64 string.
     *
     * @return string The decoded bytes.
     *
     * @throws CaseDocumentException When the input is not valid base64.
     */
    private function decode(string $base64): string
    {
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            throw new CaseDocumentException('The supplied inhoud is not valid base64.');
        }

        return $decoded;
    }//end decode()

    /**
     * Strips path separators from a name so it stays inside the intended folder.
     *
     * @param string $name The raw name.
     *
     * @return string The sanitised name.
     */
    private function sanitiseName(string $name): string
    {
        $name = str_replace(['/', '\\', "\0"], '', $name);
        $name = trim($name);

        if ($name === '' || $name === '.' || $name === '..') {
            return 'document';
        }

        return $name;
    }//end sanitiseName()
}//end class
