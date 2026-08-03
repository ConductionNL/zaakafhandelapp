<?php

/**
 * PHPStan bootstrap file - registers OCP autoloader for static analysis.
 */

$autoloader = require __DIR__ . '/vendor/autoload.php';
$autoloader->addPsr4('OCP\\', __DIR__ . '/vendor/nextcloud/ocp/OCP/');
$autoloader->addPsr4('NCU\\', __DIR__ . '/vendor/nextcloud/ocp/NCU/');

// OpenRegister is a sibling Nextcloud app rather than a composer dependency, so
// PHPStan cannot reflect any OCA\OpenRegister\* symbol. The "unknown class"
// patterns in phpstan.neon cover most of that, but a `@throws` tag naming an
// OpenRegister exception yields `throws.notThrowable`, which those patterns do
// not match - PHPStan cannot see that the class extends \Exception. Map the
// namespace at analysis time onto faithful stubs so the tag type-checks against
// reality instead of being suppressed. Only classes with a file under
// phpstan-stubs/OpenRegister/ resolve; everything else stays unknown as before.
// This mapping lives inside PHPStan's process only - Nextcloud autoloads the
// real OpenRegister classes at runtime.
$autoloader->addPsr4('OCA\\OpenRegister\\', __DIR__ . '/phpstan-stubs/OpenRegister/');
