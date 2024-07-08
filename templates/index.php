<?php

use OCP\Util;

$appId = OCA\ZaakAfhandelApp\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-mainScript');
Util::addStyle($appId, 'main');
?>


<div id="zaakafhandelapp"></div>