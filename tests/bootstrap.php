<?php

/**
 * PHPUnit bootstrap for ZaakAfhandelApp unit tests.
 *
 * Registers the Composer autoloader so the `OCA\ZaakAfhandelApp\` PSR-4
 * namespace (mapped to `lib/` in composer.json) resolves inside the test
 * suite. The portal contribution provider is a plain dependency-free class, so
 * no Nextcloud server bootstrap is required for those tests; OCP interfaces
 * and OpenRegister collaborators used by the ZGW services are resolved from
 * stubs registered below.
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
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

define('PHPUNIT_RUN', 1);

require_once __DIR__.'/../vendor/autoload.php';

// Recover the Composer ClassLoader instance from the registered autoload
// functions. require'ing vendor/autoload.php returns `true` (not the loader)
// when PHPUnit has already booted it, so fetch it from spl_autoload_functions.
$autoloader = null;
foreach (spl_autoload_functions() as $loader) {
    if (is_array($loader) === true && $loader[0] instanceof \Composer\Autoload\ClassLoader) {
        $autoloader = $loader[0];
        break;
    }
}

// Register the OCP and NCU namespaces from the nextcloud/ocp stub package so
// PHPUnit can mock OCP interfaces without a full Nextcloud installation. These
// self-skip (class_exists guards inside the stubs) when the real Nextcloud
// runtime is loaded below.
if ($autoloader !== null && is_dir(__DIR__.'/../vendor/nextcloud/ocp/OCP') === true) {
    $autoloader->addPsr4('OCP\\', __DIR__.'/../vendor/nextcloud/ocp/OCP/');
    $autoloader->addPsr4('NCU\\', __DIR__.'/../vendor/nextcloud/ocp/NCU/');
}

// These are pure unit tests: collaborators are mocked and no live Nextcloud
// server (and therefore no DB, config, or filesystem) is needed. We do NOT boot
// lib/base.php — doing so requires write access to the instance config dir and
// drags in the whole runtime. Instead the OCP interfaces come from the
// nextcloud/ocp stub package registered above, and the OpenRegister classes the
// ZGW services type-hint (ObjectEntity, ObjectService, RegisterMapper,
// SchemaMapper, Schema, CustomValidationException) are resolved from the
// lightweight, signature-matching stubs under tests/Stubs/.
//
// The OCA\OpenRegister\ => tests/Stubs/ PSR-4 mapping deliberately lives ONLY
// here (test-time) and NOT in composer.json autoload-dev: a dev-built vendor/
// (the shared dev instance builds with dev) would otherwise fold the stubs into
// the runtime classmap and SHADOW the real OpenRegister classes instance-wide,
// 500ing every app. See openregister#2036 / hermiq#21. Registering on the
// loader here is lazy (findFile only resolves when a class is actually
// requested), so ordering vs the OCP registration above is fine. We register
// unconditionally — do NOT guard with class_exists(): a failed class_exists()
// probe against another registered autoloader (e.g. phpstan's PharAutoloader)
// poisons resolution so a subsequent addPsr4() no longer takes effect.
if ($autoloader !== null) {
    $autoloader->addPsr4('OCA\\OpenRegister\\', __DIR__.'/Stubs/');
}
