<?php

/**
 * Test stub for OCA\OpenRegister\Exception\CustomValidationException.
 *
 * Fallback used only when the real OpenRegister app is NOT loaded. Mirrors the
 * real constructor (message + errors array) and getErrors() accessor so the
 * tests can assert on validation failures. No-op when the real class exists.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\OpenRegister\Exception;

use Exception;

/**
 * Stub for CustomValidationException.
 */
class CustomValidationException extends Exception {

	/**
	 * @var array<int,array<string,mixed>> The validation errors.
	 */
	private array $errors;

	/**
	 * Constructor.
	 *
	 * @param string $message The error message.
	 * @param array<int,array<string,mixed>> $errors The validation errors.
	 */
	public function __construct(string $message, array $errors) {
		parent::__construct($message);
		$this->errors = $errors;
	}//end __construct()

	/**
	 * Return the validation errors.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function getErrors(): array {
		return $this->errors;
	}//end getErrors()
}//end class
