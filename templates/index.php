<?php

use OCP\Util;

$appId = OCA\ZaakAfhandelApp\AppInfo\Application::APP_ID;
Util::addScript($appId, $appId . '-main');
Util::addStyle($appId, 'main');
?>


<div id="zaakafhandelapp"></div>