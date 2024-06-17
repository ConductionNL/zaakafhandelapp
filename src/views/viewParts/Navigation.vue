<template>
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
						<li><a href="#">Configruatie</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</template>

