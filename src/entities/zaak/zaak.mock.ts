import type { TZaak } from './zaak.types'

import { Zaak } from './zaak'

/**
 *
 */
export function mockZaakData(): TZaak[] {
	return [
		{
			id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
			uuid: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
			omschrijving: 'Zaak 3',
			url: 'http://example.com',
			identificatie: 'ZAAK-2024-3',
			bronorganisatie: 'string',
			toelichting: 'string',
			zaaktype: '0a3a0ffb-dc03-4aae-b207-0ed1502e60da',
			archiefstatus: 'gearchiveerd_procestermijn_onbekend',
			registratiedatum: '2019-08-24',
			verantwoordelijkeOrganisatie: 'string',
			startdatum: '2019-08-24',
			einddatum: '2019-08-24',
			einddatumGepland: '2019-08-24',
			uiterlijkeEinddatumAfdoening: '2019-08-24',
			publicatiedatum: '2019-08-24',
			communicatiekanaal: 'http://example.com',
			betalingsindicatie: 'nvt',
			betalingsindicatieWeergave: 'string',
			laatsteBetaaldatum: '2019-08-24T14:15:22Z',
			selectielijstklasse: 'http://example.com',
			hoofdzaak: 'http://example.com',
			klant: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
			berichten: [],
			opschorting: { indicatie: false, reden: '' },
			verlenging: { reden: '', duur: '' },
		},
	]
}

/**
 *
 * @param data
 */
export function mockZaak(data: TZaak[] = mockZaakData()): TZaak[] {
	return data.map((item) => new Zaak(item))
}
