<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-berichtenScript');
Util::addStyle($appId, 'main');
?>
<div id="berichten"></div>
