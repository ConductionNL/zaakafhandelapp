<?php

/**
 * Unit tests for CaseDocumentService.
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

namespace OC\Hooks {
	if (interface_exists(\OC\Hooks\Emitter::class, false) === false) {
		/**
		 * Minimal stub of the private OC\Hooks\Emitter interface so PHPUnit can
		 * mock OCP\Files\IRootFolder (which extends it) without a full Nextcloud
		 * runtime. Self-skips when the real interface is present.
		 */
		interface Emitter {
		}
	}
}

namespace OC\User {
	if (class_exists(\OC\User\NoUserException::class, false) === false) {
		/**
		 * Minimal stub of OC\User\NoUserException referenced by IRootFolder.
		 */
		class NoUserException extends \Exception {
		}
	}
}

namespace OCA\ZaakAfhandelApp\Tests\Unit\Service {

	use OCA\ZaakAfhandelApp\Service\CaseDocumentException;
	use OCA\ZaakAfhandelApp\Service\CaseDocumentService;
	use OCP\Files\File;
	use OCP\Files\Folder;
	use OCP\Files\IRootFolder;
	use OCP\IUser;
	use OCP\IUserSession;
	use PHPUnit\Framework\TestCase;

	/**
	 * Locks the NC-Files side of the DRC: base64 round-trip + size derivation on
	 * write, lazy per-zaak folder creation, content replacement, missing-file
	 * tolerance on delete, and the typed exception on invalid base64 (no
	 * silent-null fail-open).
	 */
	class CaseDocumentServiceTest extends TestCase {

		/**
		 * @var IRootFolder&\PHPUnit\Framework\MockObject\MockObject
		 */
		private $rootFolder;

		/**
		 * @var CaseDocumentService
		 */
		private $service;

		protected function setUp(): void {
			$this->rootFolder = $this->createMock(IRootFolder::class);

			$user = $this->createMock(IUser::class);
			$user->method('getUID')->willReturn('alice');
			$session = $this->createMock(IUserSession::class);
			$session->method('getUser')->willReturn($user);

			$this->service = new CaseDocumentService($this->rootFolder, $session);
		}//end setUp()

		public function testWriteDocumentDecodesBase64AndDerivesSize(): void {
			$bytes = 'hello world';
			$base64 = base64_encode($bytes);

			$file = $this->createMock(File::class);
			$file->method('getId')->willReturn(4242);
			$file->method('getSize')->willReturn(strlen($bytes));

			$zaakFolder = $this->createMock(Folder::class);
			$zaakFolder->method('nodeExists')->with('brief.txt')->willReturn(false);
			$zaakFolder->expects($this->once())
				->method('newFile')
				->with('brief.txt', $bytes)
				->willReturn($file);

			$userFolder = $this->createMock(Folder::class);
			$userFolder->method('nodeExists')->with('Zaakdocumenten/zaak-1')->willReturn(false);
			$userFolder->method('newFolder')->with('Zaakdocumenten/zaak-1')->willReturn($zaakFolder);

			$this->rootFolder->method('getUserFolder')->with('alice')->willReturn($userFolder);

			$result = $this->service->writeDocument('zaak-1', 'brief.txt', $base64);

			$this->assertSame(4242, $result['fileId']);
			$this->assertSame(strlen($bytes), $result['bestandsomvang']);
		}//end testWriteDocumentDecodesBase64AndDerivesSize()

		public function testWriteDocumentRejectsInvalidBase64(): void {
			$this->expectException(CaseDocumentException::class);

			$this->service->writeDocument('zaak-1', 'brief.txt', '!!!not-base64!!!');
		}//end testWriteDocumentRejectsInvalidBase64()

		public function testReplaceContentWritesNewBytesAndReturnsSize(): void {
			$bytes = 'updated';

			$file = $this->createMock(File::class);
			$file->expects($this->once())->method('putContent')->with($bytes);
			$file->method('getSize')->willReturn(strlen($bytes));

			$userFolder = $this->createMock(Folder::class);
			$userFolder->method('getById')->with(4242)->willReturn([$file]);
			$this->rootFolder->method('getUserFolder')->willReturn($userFolder);

			$this->assertSame(strlen($bytes), $this->service->replaceContent(4242, base64_encode($bytes)));
		}//end testReplaceContentWritesNewBytesAndReturnsSize()

		public function testDeleteDocumentTrueWhenFilePresent(): void {
			$file = $this->createMock(File::class);
			$file->expects($this->once())->method('delete');

			$userFolder = $this->createMock(Folder::class);
			$userFolder->method('getById')->with(7)->willReturn([$file]);
			$this->rootFolder->method('getUserFolder')->willReturn($userFolder);

			$this->assertTrue($this->service->deleteDocument(7));
		}//end testDeleteDocumentTrueWhenFilePresent()

		public function testDeleteDocumentFalseWhenFileAlreadyGone(): void {
			$userFolder = $this->createMock(Folder::class);
			$userFolder->method('getById')->with(7)->willReturn([]);
			$this->rootFolder->method('getUserFolder')->willReturn($userFolder);

			$this->assertFalse($this->service->deleteDocument(7));
		}//end testDeleteDocumentFalseWhenFileAlreadyGone()
	}//end class

}
