<?php


?>

<!-- Based on -->
<div id="app-navigation" class="">

	<div class="app-navigation-new">
		<button type="button" class="icon-add">
			Zaak Aanmaken
		</button>
	</div>

	<!-- Your navigation here -->
	<ul id="usergrouplist">
		<li>
			<a href="#">Zaken</a>
			<div class="app-navigation-entry-utils">
				<ul>
					<li class="app-navigation-entry-utils-counter">999</li>
					<li class="app-navigation-entry-utils-menu-button">
						<button>Zaak Aanmaken</button>
					</li>
				</ul>
			</div>
		</li>
		<li><a href="#">Taken</a></li>
		<li><a href="#">Klanten</a></li>
		<li>
			<a href="#">Contact Momenten</a>
			<ul>
				<li><a href="#">Second level entry</a></li>
				<li><a href="#">Second level entry</a></li>
			</ul>
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
					<li><a href="#">Zaak Typen</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>



<div id="app-content">
	<div id="app-navigation-toggle" class="icon-menu"></div>
	<!-- app-content-wrapper is optional, only use if app-content-list  -->
	<div id="app-content-wrapper">
		<div class="app-content-list">
			<a href="#" class="app-content-list-item">
				<div class="app-content-list-item-star icon-starred"></div>
				<div class="app-content-list-item-icon" style="background-color: rgb(41, 97, 156);">N</div>
				<div class="app-content-list-item-line-one">Brandveiligheids checks</div>
				<div class="app-content-list-item-line-two">Samenwerkings verzoek</div>
				<div class="app-content-list-item-line-two"><progress value="50" max="100"></progress></div>
				<span class="app-content-list-item-details">8 days left</span>
				<div class="icon-more"></div>
			</a>
		</div>
		<div class="app-content-detail">
		</div>
	</div>
</div>
 <div id="app-sidebar">
	 Selecteer een zaak
 </div>
