<?php

/**
 * PHPUnit bootstrap for zaakafhandelapp unit tests.
 *
 * Registers the Composer autoloader so the `OCA\ZaakAfhandelApp\` PSR-4
 * namespace (mapped to `lib/` in composer.json) resolves inside the test
 * suite. The portal contribution provider is a plain dependency-free class, so
 * no Nextcloud server bootstrap is required for these tests.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://conduction.nl
 */

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';
