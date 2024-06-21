<?php

use OCP\Util;

$appId = OCA\DsoNextcloud\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-takenScript');
Util::addStyle($appId, 'main');
?>
<div id="taken"></div>
