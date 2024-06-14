<?php

return [
	'routes' => [
		[
			'name' => 'page#mainPage',
			'url' => '/', 'verb' => 'GET'
		],
		[
			'name' => 'zaken#index',
			'url' => '/zaken', 'verb' => 'GET'
		],
		[
			'name' => 'zaakTypen#index',
			'url' => '/zaak_typen', 'verb' => 'GET'
		],
		[
			'name' => 'taken#index',
			'url' => '/taken', 'verb' => 'GET'
		],
		[
			'name' => 'klanten#index',
			'url' => '/klanten', 'verb' => 'GET'
		],
		[
			'name' => 'contactMomenten#index',
			'url' => '/contact_momenten', 'verb' => 'GET'
		],
	],
];
