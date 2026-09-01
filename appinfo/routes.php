<?php

return [
	'resources' => [
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/redoc-1.5.1
		'zaken' => ['url' => 'api/zrc/zaken'],
		'resultaten' => ['url' => 'api/zrc/resultaten'],
		'rollen' => ['url' => 'api/zrc/rollen'],
		'statusen' => ['url' => 'api/zrc/statussen'],
		'zaakInformatieObjecten' => ['url' => 'api/zrc/zaakinformatieobjecten'],
		'zaakObjecten' => ['url' => 'api/zrc/zaakobjecten'],
		// Route placeholders MUST match the controller method parameter names
		// exactly — NC's dispatcher binds route params to method args by name,
		// so a `{zaak_uuid}` placeholder against a `$zaakUuid`/`$zaakId` arg
		// binds null and the typed arg throws a TypeError (HTTP 500) before the
		// method body runs. ZaakBesluiten/ZaakAuditTrail take $zaakUuid;
		// ZaakEigenschappen takes $zaakId.
		'zaakBesluiten' => ['url' => 'api/zrc/zaken/{zaakUuid}/besluiten'],
		'zaakEigenschappen' => ['url' => 'api/zrc/zaken/{zaakId}/eigenschappen'],
		// zaakAuditTrail is read-only per ZRC; the resource quintet stays registered for gate-14, write verbs return 405.
		'zaakAuditTrail' => ['url' => 'api/zrc/zaken/{zaakUuid}/audit_trail'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/catalogi/redoc-1.3.1
		'zaakTypen' => ['url' => 'api/ztc/zaaktypen'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/documenten/redoc-1.5.0
		'documenten' => ['url' => 'api/drc/enkelvoudiginformatieobjecten'],
		// Conform https://vng-realisatie.github.io/gemma-zaken/standaard/besluiten/redoc-1.0.2
		'besluiten' => ['url' => 'api/brc'],
		// Conform ???
		'taken' => ['url' => 'api/taken'],
		'klanten' => ['url' => 'api/klanten'],
		'berichten' => ['url' => 'api/berichten'],
		'contactMomenten' => ['url' => 'api/contactmomenten'],
		'medewerkers' => ['url' => 'api/medewerkers'],
		// `dashboard` is deliberately NOT a resource. DashboardController serves
		// the SPA shell only (`dashboard#page` below); its api/dashboard quintet
		// returned a hardcoded demo constant with no caller in `src/` and was
		// removed rather than guarded — see the class docblock and
		// ConductionNL/zaakafhandelapp#347.
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

		// Addressbook integration routes (klanten-addressbook-sync).
		// Served by KlantContactsController — split out of KlantenController so
		// klant CRUD and the addressbook surface stay separate concerns. The URLs
		// are unchanged; only the controller half of the route name moved.
		['name' => 'klantContacts#contactsStatus', 'url' => '/api/klanten/contacts/status', 'verb' => 'GET'],
		['name' => 'klantContacts#searchContacts', 'url' => '/api/klanten/contacts/search', 'verb' => 'GET'],
		['name' => 'klantContacts#importContact', 'url' => '/api/klanten/contacts/import', 'verb' => 'POST'],
		['name' => 'klantContacts#exportContact', 'url' => '/api/klanten/{id}/contacts/export', 'verb' => 'POST'],

		// Page routes
		['name' => 'dashboard#page', 'url' => '/', 'verb' => 'GET'],
		['name' => 'configuration#index', 'url' => '/api/configuration', 'verb' => 'GET'],
		['name' => 'configuration#save', 'url' => '/api/configuration', 'verb' => 'POST'],
		['name' => 'contactMomenten#page', 'url' => '/contactmomenten', 'verb' => 'GET'],
		['name' => 'contactMomenten#page', 'postfix' => 'details', 'url' => '/contactmomenten/{id}', 'verb' => 'GET'],
		['name' => 'zaken#page', 'url' => '/zaken', 'verb' => 'GET'],
		['name' => 'zaken#page', 'postfix'  => 'details', 'url' => '/zaken/{id}', 'verb' => 'GET'],
		['name' => 'rollen#page', 'url' => '/rollen', 'verb' => 'GET'],
		['name' => 'rollen#page', 'postfix'  => 'details', 'url' => '/rollen/{id}', 'verb' => 'GET'],
		['name' => 'statusen#page', 'url' => '/statussen', 'verb' => 'GET'],
		['name' => 'statusen#page', 'postfix' => 'details', 'url' => '/statussen/{id}', 'verb' => 'GET'],
		['name' => 'besluiten#page', 'url' => '/besluiten', 'verb' => 'GET'],
		['name' => 'besluiten#page', 'postfix' => 'details', 'url' => '/besluiten/{id}', 'verb' => 'GET'],
		['name' => 'documenten#page', 'url' => '/documenten', 'verb' => 'GET'],
		['name' => 'documenten#page', 'postfix' => 'details', 'url' => '/documenten/{id}', 'verb' => 'GET'],
		// DRC enkelvoudiginformatieobject content download (streams the backing Nextcloud file).
		['name' => 'documenten#download', 'url' => '/api/drc/enkelvoudiginformatieobjecten/{id}/download', 'verb' => 'GET'],
		['name' => 'resultaten#pages', 'url' => '/resultaten', 'verb' => 'GET'],
		['name' => 'resultaten#pages', 'postfix' => 'details', 'url' => '/resultaten/{id}', 'verb' => 'GET'],
		['name' => 'zaakInformatieObjecten#page', 'url' => '/zaakinformatieobjecten', 'verb' => 'GET'],
		['name' => 'zaakTypen#page','url' => '/zaaktypen', 'verb' => 'GET'],
		['name' => 'zaakTypen#page','postfix' => 'details', 'url' => '/zaaktypen/{id}', 'verb' => 'GET'],
		['name' => 'taken#page','url' => '/taken', 'verb' => 'GET'],
		['name' => 'taken#page','postfix' => 'details', 'url' => '/taken/{id}', 'verb' => 'GET'],
		['name' => 'klanten#page','url' => '/klanten', 'verb' => 'GET'],
		['name' => 'klanten#page','postfix' => 'details', 'url' => '/klanten/{id}', 'verb' => 'GET'],
		['name' => 'medewerkers#page','url' => '/medewerkers', 'verb' => 'GET'],
		['name' => 'medewerkers#page','postfix' => 'details', 'url' => '/medewerkers/{id}', 'verb' => 'GET'],
		['name' => 'berichten#page','url' => '/berichten', 'verb' => 'GET'],
		['name' => 'berichten#page','postfix' => 'details', 'url' => '/berichten/{id}', 'verb' => 'GET'],
		['name' => 'dashboard#page', 'postfix' => 'search', 'url' => '/zoeken', 'verb' => 'GET'],
		// user Settings
		['name' => 'settings#index','url' => '/settings', 'verb' => 'GET'],
		['name' => 'settings#create', 'url' => '/settings', 'verb' => 'POST'],
		// Generic per-user preferences (used by shared nextcloud-vue widgets, e.g. CnSupportDialog).
		['name' => 'preferences#getPreference', 'url' => '/api/preferences/{key}', 'verb' => 'GET'],
		['name' => 'preferences#setPreference', 'url' => '/api/preferences/{key}', 'verb' => 'PUT'],
		// User
		['name' => 'users#me', 'url' => '/me', 'verb' => 'GET'],
		// Object API routes
		['name' => 'objects#index', 'url' => 'api/objects/{objectType}', 'verb' => 'GET'],
		['name' => 'objects#create', 'url' => 'api/objects/{objectType}', 'verb' => 'POST'],
		['name' => 'objects#show', 'url' => 'api/objects/{objectType}/{id}', 'verb' => 'GET'],
		['name' => 'objects#update', 'url' => 'api/objects/{objectType}/{id}', 'verb' => 'PUT'],
		['name' => 'objects#destroy', 'url' => 'api/objects/{objectType}/{id}', 'verb' => 'DELETE'],
		['name' => 'objects#getAuditTrail', 'url' => 'api/objects/{objectType}/{id}/audit', 'verb' => 'GET'],
		['name' => 'objects#getRelations', 'url' => 'api/objects/{objectType}/{id}/relations', 'verb' => 'GET'],
		['name' => 'objects#getUses', 'url' => 'api/objects/{objectType}/{id}/uses', 'verb' => 'GET'],

		// SPA catch-all — MUST stay last so every explicit route above keeps
		// priority over the /{path} fallback. Without it only the enumerated
		// page routes resolve, and anything else (/features-roadmap,
		// /auditTrail, any detail route) 404s at the server under history
		// routing. Mirrors \OCA\OpenRegister\AppHost\Routes::standard()'s
		// own catch-all, spelled inline because this file also declares a
		// `resources` block that the builder does not carry.
		['name' => 'dashboard#catchAll', 'url' => '/{path}', 'verb' => 'GET',
			'requirements' => ['path' => '.+'], 'defaults' => ['path' => '']]
	]
];
