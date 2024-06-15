<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-zakenScript');
Util::addStyle($appId, 'main');
?>
<div id="zaken"></div>