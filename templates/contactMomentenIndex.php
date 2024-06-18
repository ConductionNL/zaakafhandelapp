<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-contactMomentenScript');
Util::addStyle($appId, 'main');
?>
<div id="contactMomenten"></div>
