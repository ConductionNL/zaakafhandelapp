<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-zaakTypenScript');
Util::addStyle($appId, 'main');
?>
<div id="zaakTypen"></div>
