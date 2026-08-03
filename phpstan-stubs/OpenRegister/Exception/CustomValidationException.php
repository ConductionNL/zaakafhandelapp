<?php

/**
 * PHPStan-only stub for OpenRegister's CustomValidationException.
 *
 * OpenRegister is a sibling Nextcloud app, not a composer dependency, so during
 * static analysis PHPStan cannot reflect any OCA\OpenRegister\* class. Most of
 * those gaps are covered by the "unknown class" ignore patterns in phpstan.neon,
 * but `@throws OCA\OpenRegister\Exception\CustomValidationException` produces a
 * different diagnostic (`throws.notThrowable`) that those patterns do not match:
 * PHPStan simply cannot see that the class extends \Exception.
 *
 * Rather than suppress a tag that is factually correct, this stub tells PHPStan
 * the truth. It mirrors the real class in openregister
 * (lib/Exception/CustomValidationException.php) exactly: same parent, same
 * constructor signature, same public accessor.
 *
 * This file is loaded ONLY by phpstan-bootstrap.php, which is only ever executed
 * inside PHPStan. Nextcloud never autoloads it, so at runtime the genuine
 * OpenRegister class is always the one in play.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

namespace OCA\OpenRegister\Exception;

use Exception;

/**
 * Exception carrying business-rule validation errors.
 */
class CustomValidationException extends Exception
{

    /**
     * The validation errors array.
     *
     * Deliberately typed as a plain array rather than copying the narrower
     * `array<string, string|array<string>>` docblock the real class carries.
     * That docblock does not describe how the class is actually used: the ZGW
     * services in this app pass a LIST of {name, code, reason} detail records,
     * which is what the ZGW error envelope requires, and the real constructor
     * enforces only `array`. Importing the inaccurate narrower shape here would
     * manufacture argument.type errors against correct code.
     *
     * @var array<array-key, mixed>
     */
    private readonly array $errors;

    /**
     * @param string                   $message The error message.
     * @param array<array-key, mixed>  $errors  The validation errors.
     */
    public function __construct(string $message, array $errors)
    {
        $this->errors = $errors;
        parent::__construct(message: $message);
    }//end __construct()

    /**
     * @return array<array-key, mixed> The validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }//end getErrors()
}//end class
