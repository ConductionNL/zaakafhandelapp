<?php

use OCP\Util;

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
					<input type="checkbox" id="test1" class="app-content-list-item-checkbox checkbox" checked="checked"><label for="test1"></label>
					<div class="app-content-list-item-icon" style="background-color: rgb(231, 192, 116);">C</div>
					<div class="app-content-list-item-line-one">Contact 1</div>
					<div class="icon-delete"></div>
				</a>
				<a href="#" class="app-content-list-item">
					<div class="app-content-list-item-star icon-starred"></div>
					<div class="app-content-list-item-icon" style="background-color: rgb(151, 72, 96);">T</div>
					<div class="app-content-list-item-line-one">Favourited task #2</div>
					<div class="icon-more"></div>
				</a>
				<a href="#" class="app-content-list-item">
					<div class="app-content-list-item-icon" style="background-color: rgb(152, 59, 144);">T</div>
					<div class="app-content-list-item-line-one">Task #2</div>
					<div class="icon-more"></div>
				</a>
				<a href="#" class="app-content-list-item">
					<div class="app-content-list-item-icon" style="background-color: rgb(31, 192, 216);">M</div>
					<div class="app-content-list-item-line-one">Important mail is very important! Don't ignore me</div>
					<div class="app-content-list-item-line-two">Hello there, here is an important mail from your mom</div>
				</a>
				<a href="#" class="app-content-list-item">
					<div class="app-content-list-item-icon" style="background-color: rgb(41, 97, 156);">N</div>
					<div class="app-content-list-item-line-one">Important mail with a very long subject</div>
					<div class="app-content-list-item-line-two">Hello there, here is an important mail from your mom</div>
					<span class="app-content-list-item-details">8 hours ago</span>
					<div class="icon-delete"></div>
				</a>
				<a href="#" class="app-content-list-item">
					<div class="app-content-list-item-icon" style="background-color: rgb(141, 197, 156);">N</div>
					<div class="app-content-list-item-line-one">New contact</div>
					<div class="app-content-list-item-line-two">blabla@bla.com</div>
					<div class="icon-delete"></div>
				</a>
			</div>
			<div class="app-content-detail">
			</div>
		</div>
	</div>
<!--  <div id="app-sidebar"></div>-->
