<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-configurationScript');
Util::addStyle($appId, 'main');
?>
<div id="configuration"></div>
