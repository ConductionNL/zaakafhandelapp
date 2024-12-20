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
		'zaakbesluiten' => ['url' => 'api/zrc/zaken/{zaak_uuid}/besluiten'],
		'zaakeigenschappen' => ['url' => 'api/zrc/zaken/{zaak_uuid}/eigenschappen'],
		'zaakaudittrail' => ['url' => 'api/zrc/zaken/{zaak_uuid}/audit_trail'],
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
		'contactmomenten' => ['url' => 'api/contactmomenten'],
		'medewerkers' => ['url' => 'api/medewerkers'],

	],
	'routes' => [
		// Audit trail routes
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
		['name' => 'contactMomenten#page', 'url' => '/contactmomenten', 'verb' => 'GET'],
		['name' => 'contactMomenten#page', 'postfix' => 'details', 'url' => '/contactmomenten/{id}', 'verb' => 'GET'],
		['name' => 'zaken#page', 'url' => '/zaken', 'verb' => 'GET'],
		['name' => 'zaken#page', 'postfix'  => 'details', 'url' => '/zaken/{id}', 'verb' => 'GET'],
		['name' => 'rollen#page', 'url' => '/rollen', 'verb' => 'GET'],
		['name' => 'statussen#page', 'url' => '/statussen', 'verb' => 'GET'],
		['name' => 'zaakinformatieobjecten#page', 'url' => '/zaakinformatieobjecten', 'verb' => 'GET'],
		['name' => 'zaakTypen#page','url' => '/zaak_typen', 'verb' => 'GET'],
		['name' => 'taken#page','url' => '/taken', 'verb' => 'GET'],
		['name' => 'taken#page','postfix' => 'details', 'url' => '/taken/{id}', 'verb' => 'GET'],
		['name' => 'klanten#page','url' => '/klanten', 'verb' => 'GET'],
		['name' => 'klanten#page','postfix' => 'details', 'url' => '/klanten/{id}', 'verb' => 'GET'],
		['name' => 'medewerkers#page','url' => '/medewerkers', 'verb' => 'GET'],
		['name' => 'medewerkers#page','postfix' => 'details', 'url' => '/medewerkers/{id}', 'verb' => 'GET'],
		['name' => 'berichten#index','url' => '/berichten', 'verb' => 'GET'],
		// user Settings
		['name' => 'settings#index','url' => '/settings', 'verb' => 'GET'],
		['name' => 'settings#create', 'url' => '/settings', 'verb' => 'POST'],
		// User
		['name' => 'users#me', 'url' => '/me', 'verb' => 'GET'],
		['name' => 'objects#index', 'url' => '/api/objects/{_objectType}', 'verb' => 'GET', 'requirements' => ['_objectType' => '[^/]+']],
		['name' => 'objects#show', 'url' => '/api/objects/{_objectType}/{id}', 'verb' => 'GET', 'requirements' => ['_objectType' => '[^/]+', 'id' => '[^/]+']],
		['name' => 'objects#getAuditTrail', 'url' => '/api/objects/{_objectType}/{id}/audit_trail', 'verb' => 'GET', 'requirements' => ['_objectType' => '[^/]+', 'id' => '[^/]+']],
		['name' => 'objects#create', 'url' => '/api/objects/{_objectType}', 'verb' => 'POST', 'requirements' => ['_objectType' => '[^/]+', 'id' => '[^/]+']],
		['name' => 'objects#update', 'url' => '/api/objects/{_objectType}/{id}', 'verb' => 'PUT', 'requirements' => ['_objectType' => '[^/]+', 'id' => '[^/]+']],
		['name' => 'objects#destroy', 'url' => '/api/objects/{_objectType}/{id}', 'verb' => 'DELETE', 'requirements' => ['_objectType' => '[^/]+', 'id' => '[^/]+']]
	]
];
