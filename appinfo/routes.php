<?php

return [
	 'resources' => [
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/redoc-1.5.1
	 	'zaken' => ['url' => 'api/zrc/zaken'],
	 	'resultaten' => ['url' => 'api/zrc/resultaten'],
	 	'rollen' => ['url' => 'api/zrc/rollen'],
	 	'statussen' => ['url' => 'api/zrc/statussen'],
	 	'zaakinformatieobjecten' => ['url' => 'api/zrc/zaakinformatieobjecten'],
	 	'zaakobjecten' => ['url' => 'api/zrc/zaakobjecten'],
		// Removed zaakbesluiten and zaakaudittrail resource routes: controllers return 501
		// until a real OR-backed implementation is in place (issue #268).
	 	'zaakeigenschappen' => ['url' => 'api/zrc/zaken/{zaak_uuid}/eigenschappen'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/catalogi/redoc-1.3.1
	 	'zaakTypen' => ['url' => 'api/ztc/zaaktypen'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/documenten/redoc-1.5.0
		// Removed documenten resource route: controller returns 501 until a real
		// DRC-backed implementation is in place (issue #268).
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/besluiten/redoc-1.0.2
	 	'besluiten' => ['url' => 'api/brc'],
		// Conform ???
	 	'taken' => ['url' => 'api/taken'],
	 	'klanten' => ['url' => 'api/klanten'],
	 	'berichten' => ['url' => 'api/berichten'],

	 ],
	'routes' => [
		// Audit trail routes (read-only — real data from ObjectService)
		['name' => 'zaken#getAuditTrail', 'url' => '/api/zaken/{id}/audit_trail', 'verb' => 'GET'],
		['name' => 'klanten#getAuditTrail', 'url' => '/api/klanten/{id}/audit_trail', 'verb' => 'GET'],
		['name' => 'berichten#getAuditTrail', 'url' => '/api/berichten/{id}/audit_trail', 'verb' => 'GET'],
		['name' => 'taken#getAuditTrail', 'url' => '/api/taken/{id}/audit_trail', 'verb' => 'GET'],

		// Overige klant routes
		['name' => 'klanten#getContactmomenten', 'url' => '/api/klanten/{id}/contactmomenten', 'verb' => 'GET'],
		['name' => 'klanten#getTaken', 'url' => '/api/klanten/{id}/taken', 'verb' => 'GET'],
		['name' => 'klanten#getBerichten', 'url' => '/api/klanten/{id}/berichten', 'verb' => 'GET'],
		['name' => 'klanten#getZaken', 'url' => '/api/klanten/{id}/zaken', 'verb' => 'GET'],

		// Page routes
		['name' => 'dashboard#page', 'url' => '/', 'verb' => 'GET'],
		['name' => 'configuration#index', 'url' => '/api/configuration', 'verb' => 'GET'],
		['name' => 'configuration#create', 'url' => '/api/configuration', 'verb' => 'POST'],
		['name' => 'zaken#page', 'url' => '/zaken', 'verb' => 'GET'],
		['name' => 'rollen#page', 'url' => '/rollen', 'verb' => 'GET'],
		['name' => 'statussen#page', 'url' => '/statussen', 'verb' => 'GET'],
		['name' => 'zaakinformatieobjecten#page', 'url' => '/zaakinformatieobjecten', 'verb' => 'GET'],
		['name' => 'zaakTypen#page','url' => '/zaak_typen', 'verb' => 'GET'],
		['name' => 'taken#page','url' => '/taken', 'verb' => 'GET'],
		['name' => 'klanten#page','url' => '/klanten', 'verb' => 'GET'],
		['name' => 'berichten#index','url' => '/berichten', 'verb' => 'GET'],
		// user Settings
		['name' => 'settings#index','url' => '/settings', 'verb' => 'GET'],
		['name' => 'settings#create', 'url' => '/settings', 'verb' => 'POST'],
	]
];
