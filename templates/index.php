<?php

use OCP\Util;

$appId = OCA\ZaakAfhandelApp\AppInfo\Application::APP_ID;

// webpack splitChunks (see webpack.config.js) emits a runtime chunk plus two
// shared chunks (`shared-vendor`, `shared-nc-vue`). The main entry's bundle
// tail wraps the Vue mount in `__webpack_require__.O(0, [shared chunks], …)`
// which only fires once all listed chunks have registered themselves on
// `self.webpackChunkzaakafhandelapp`. If we only `addScript` the main entry,
// the shared chunks never load, the callback never fires, and the Vue app
// silently fails to mount (issue: tutorial capture spec saw 0/52 real
// screenshots, only NC chrome). Register every chunk produced by splitChunks
// here, in dependency order. The runtime chunk owns `__webpack_require__.O`
// and must load before consumers can drain their queues.
Util::addScript($appId, $appId . '-runtime');
Util::addScript($appId, $appId . '-shared-vendor');
Util::addScript($appId, $appId . '-shared-nc-vue');
Util::addScript($appId, $appId . '-main');
Util::addStyle($appId, 'main');
?>


<?php
// ⚠️ The host element is `zaakafhandelapp-app`, NOT `content`.
//
// Vue 2's `$mount('#content')` REPLACED the matched element, so this div being
// a duplicate of Nextcloud's own `layout.user.php` `#content` wrapper never
// showed. Vue 3's `mount()` renders INSIDE the match, so keeping the id would
// make the app render inside core's wrapper and break the NcContent layout —
// with no error. Renaming the host sidesteps which div wins entirely.
?>
<div id="zaakafhandelapp-app"></div>
