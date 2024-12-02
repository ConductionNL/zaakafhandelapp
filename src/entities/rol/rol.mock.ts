import { Rol } from './rol'
import { TRol } from './rol.types'

export const mockRolData = (): TRol[] => [
	{
		id: '1',
		uuid: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		omschrijving: 'Zaak 3',
		omschrijvingGeneriek: 'Zaak 3',
		url: 'http://example.com',
		zaak: 'ZAAK-2024-3',
		betrokkene: 'string',
		betrokkeneType: 'string',
		afwijkendeNaamBetrokkene: '0a3a0ffb-dc03-4aae-b207-0ed1502e60da',
		roltype: 'gearchiveerd_procestermijn_onbekend',
		roltoelichting: '2019-08-24',
		registratiedatum: 'string',
		indicatieMachtiging: '2019-08-24',
		contactpersoonRol: {
			emailadres: 'test@gmail.com',
			functie: 'something',
			telefoonnummer: '06 123456789',
			naam: 'Henry',
		},
		statussen: ['active', 'nieuw'],
		_expand: {
			zaak: '',
			roltype: '',
			statussen: '',
		},
		betrokkeneIdentificatie: {
			identificatie: '',
			achternaam: '',
			voorletters: '',
			voorvoegselAchternaam: '',
		},
	},
]

export const mockRol = (data: TRol[] = mockRolData()): TRol[] => data.map(item => new Rol(item))
