<template>
  <div class="container">
	  <div id="app-navigation" class="">
		  <div class="app-navigation-new">
			  <button type="button" class="icon-add">
				  Zaak Aanmaken
			  </button>
		  </div>

		  <!-- Your navigation here -->
		  <ul id="usergrouplist">
			  <li class="app-navigation-entry active">
				  <a class="app-navigation-entry-link" href="/index.php/apps/dsonextcloud/zaken">Zaken</a>
				  <div class="app-navigation-entry-utils">
					  <ul>
						  <li class="app-navigation-entry-utils-counter"></li>
					  </ul>
				  </div>
			  </li>
			  <li>
				  <a href="/index.php/apps/dsonextcloud/taken">Taken</a>
			  </li>
			  <li><a href="/index.php/apps/dsonextcloud/klanten">Klanten</a></li>
			  <li>
				  <a href="/index.php/apps/dsonextcloud/contact_momenten">Contact Momenten</a>
			  </li>
		  </ul>

		  <div id="app-settings">
			  <!-- app settings -->
			  <div id="app-settings-header">
				  <button class="settings-button" data-apps-slide-toggle="#app-settings-content">
					  Settings
				  </button>
			  </div>
			  <div id="app-settings-content">
				  <div class="app-navigation-new">
					  <ul>
						  <li><a href="/index.php/apps/dsonextcloud/zaak_typen">Zaak Typen</a></li>
					  </ul>
				  </div>
			  </div>
		  </div>
	  </div>



    <div id="app-content">
      <!-- app-content-wrapper is optional, only use if app-content-list  -->
      <div class="zakenContainer">
        <h1 class="h1">Zaak Typen</h1>
        <ZakenOverviewTable />
      </div>
    </div>
  </div>
</template>
<script>
import ZakenOverviewTable from './viewParts/ZakenOverviewTable.vue';


export default {
  name: "app",
  components: {
    ZakenOverviewTable
  }

}
</script>
<style>
.container {
  display: flex;
  width: 100%;
}

.h1 {
  display: block !important;
  font-size: 2em !important;
  margin-block-start: 0.67em !important;
  margin-block-end: 0.67em !important;
  margin-inline-start: 0px !important;
  margin-inline-end: 0px !important;
  font-weight: bold !important;
  unicode-bidi: isolate !important;
}

.zakenContainer {
  margin-block-start: 20px;
  margin-inline-start: 20px;
  margin-inline-end: 20px;
}
</style>
