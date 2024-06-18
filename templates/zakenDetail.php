<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-zakenDetailScript');
Util::addStyle($appId, 'main');
?>

<div id="zakenDetail"></div>