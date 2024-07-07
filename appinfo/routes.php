<?php

return [
	 'resources' => [
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/redoc-1.5.1
	 	'zaken' => ['url' => 'api/zrc/zaken'],
	 	'resultaten' => ['url' => 'api/zrc/resultaten'],
	 	'rollen' => ['url' => 'api/zrc/rollen'],
	 	'statussen' => ['url' => 'api/zrc/statussen'],
	 	'zaakinformatieobjecten' => ['url' => 'api/zrc/zaakinformatieobjecten'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/catalogi/redoc-1.3.1
	 	'zaakTypen' => ['url' => 'api/ztc'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/documenten/redoc-1.5.0
	 	'documenten' => ['url' => 'api/drc'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/besluiten/redoc-1.0.2
	 	'besluiten' => ['url' => 'api/brc'],
		// Conform ???
	 	'zaakTypen' => ['url' => 'api/ztc/zaaktypen'],
		 // Conform ???
	 	'taken' => ['url' => 'api/taken'],
	 	'klanten' => ['url' => 'api/klanten'],
	 	'berichten' => ['url' => 'api/berichten'],
	 ],
	'routes' => [
		['name' => 'dashboard#page', 'url' => '/', 'verb' => 'GET'],
		['name' => 'configuration#show', 'url' => '/api/configuration', 'verb' => 'GET'],
		['name' => 'configuration#update', 'url' => '/api/configuration', 'verb' => 'PUT'],
		['name' => 'zaken#page', 'url' => '/zaken', 'verb' => 'GET'],
		['name' => 'rollen#page', 'url' => '/rollen', 'verb' => 'GET'],
		['name' => 'statussen#page', 'url' => '/statussen', 'verb' => 'GET'],
		['name' => 'zaakinformatieobjecten#page', 'url' => '/zaakinformatieobjecten', 'verb' => 'GET'],		
		['name' => 'zaakTypen#page','url' => '/zaak_typen', 'verb' => 'GET'],
		['name' => 'taken#page','url' => '/taken', 'verb' => 'GET'],
		['name' => 'klanten#page','url' => '/klanten', 'verb' => 'GET'],
		['name' => 'berichten#index','url' => '/berichten', 'verb' => 'GET'],
	]
];
