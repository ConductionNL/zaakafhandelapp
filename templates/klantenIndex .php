<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-klantenScript');
Util::addStyle($appId, 'main');
?>
<div id="klanten"></div>
