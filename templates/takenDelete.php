<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-mainScript');
Util::addStyle($appId, 'main');
?>

Taken
