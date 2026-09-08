<?php

/**
 * Test stub for OCA\OpenRegister\Db\Register.
 *
 * Fallback used only when the real OpenRegister app is NOT loaded. Concrete so
 * the tests can build a register with the slug and application stamp
 * ZGWZaakEventHandler scopes on. No-op when the real class exists.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\OpenRegister\Db;

/**
 * Stub for Register supporting the slug and application accessors the tests use.
 */
class Register {

	/**
	 * @var string|null The register slug.
	 */
	private ?string $slug = null;

	/**
	 * @var string|null The id of the app that provisioned the register.
	 */
	private ?string $application = null;

	/**
	 * Set the register slug.
	 *
	 * @param string|null $slug The slug.
	 *
	 * @return void
	 */
	public function setSlug(?string $slug): void {
		$this->slug = $slug;
	}//end setSlug()

	/**
	 * Get the register slug.
	 *
	 * @return string|null
	 */
	public function getSlug(): ?string {
		return $this->slug;
	}//end getSlug()

	/**
	 * Set the id of the app that provisioned this register.
	 *
	 * @param string|null $application The app id.
	 *
	 * @return void
	 */
	public function setApplication(?string $application): void {
		$this->application = $application;
	}//end setApplication()

	/**
	 * Get the id of the app that provisioned this register.
	 *
	 * @return string|null
	 */
	public function getApplication(): ?string {
		return $this->application;
	}//end getApplication()
}//end class
