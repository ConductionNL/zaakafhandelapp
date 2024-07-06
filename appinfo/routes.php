<?php

return [
	 'resources' => [
	 	'zaken' => ['url' => 'api/zrc'],
	 	'zaakTypen' => ['url' => 'api/ztc'],
	 	'documenten' => ['url' => 'api/drc'],
	 	'besluiten' => ['url' => 'api/brc'],
	 	'zaakTypen' => ['url' => 'api/orc'],
	 	'taken' => ['url' => 'api/taken'],
	 	'klanten' => ['url' => 'api/klanten'],
	 	'berichten' => ['url' => 'api/berichten'],
	 ],
	'routes' => [
		['name' => 'page#mainPage', 'url' => '/', 'verb' => 'GET'],
		['name' => 'configuration#index', 'url' => '/configuration', 'verb' => 'GET'],
		['name' => 'configuration#show', 'url' => '/configuration/api', 'verb' => 'GET'],
		['name' => 'configuration#update', 'url' => '/configuration/api', 'verb' => 'PUT'],
		['name' => 'zaken#index', 'url' => '/zaken', 'verb' => 'GET'],
		['name' => 'zaken#api', 'url' => '/zaken/api', 'verb' => 'GET'],
		['name' => 'zaken#detail', 'url' => '/zaken/{id}', 'verb' => 'GET'],
		['name' => 'zaakTypen#index','url' => '/zaak_typen', 'verb' => 'GET'],
		['name' => 'taken#index','url' => '/taken', 'verb' => 'GET'],
		['name' => 'klanten#index','url' => '/klanten', 'verb' => 'GET'],
		['name' => 'contactMomenten#index','url' => '/contact_momenten', 'verb' => 'GET'],
	]
];
