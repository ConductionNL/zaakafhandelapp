<?php
use OCP\Util;

$appId = OCA\ZaakAfhandelApp\AppInfo\Application::APP_ID;
// Load the webpack runtime + shared chunks before the settings entry, in
// dependency order (same as templates/index.php). webpack.config.js uses
// runtimeChunk:'runtime' + splitChunks (shared-vendor / shared-nc-vue); the
// settings entry's mount is wrapped in `__webpack_require__.O(...)` which the
// runtime chunk owns. Without the runtime + shared chunks the callback never
// fires and the page renders blank — this is why adopting CnAdminSettingsShell
// (which pulls the shared chunks) surfaced the missing-chunk bug.
Util::addScript($appId, $appId . '-runtime');
Util::addScript($appId, $appId . '-shared-vendor');
Util::addScript($appId, $appId . '-shared-nc-vue');
Util::addScript($appId, $appId . '-settings');
Util::addStyle($appId, 'main');

?>

<div id="zaakafhandelapp-settings"></div>