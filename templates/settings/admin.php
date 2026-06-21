<?php
use OCP\Util;

$appId = OCA\ZaakAfhandelApp\AppInfo\Application::APP_ID;
// Load the shared webpack chunks before the settings entry — the admin page
// now uses CnAdminSettingsShell (from @conduction/nextcloud-vue), whose deps
// are split into the shared-vendor / shared-nc-vue chunks. Without these the
// settings bundle can't resolve them at runtime and the page renders blank.
Util::addScript($appId, $appId . '-shared-vendor');
Util::addScript($appId, $appId . '-shared-nc-vue');
Util::addScript($appId, $appId . '-settings');
Util::addStyle($appId, 'main');

?>

<div id="settings"></div>