import { Rol } from './rol'
import { TRol } from './rol.types'

export const mockRolData = (): TRol[] => [
	{
		id: '1',
		uuid: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		omschrijving: 'Zaak 3',
		omschrijvingGeneriek: 'adviseur',
		url: 'http://example.com/rollen/1',
		zaak: 'http://example.com/zaken/ZAAK-2024-3',
		betrokkene: 'http://example.com/betrokkenen/0a3a0ffb',
		betrokkeneType: 'natuurlijk_persoon',
		afwijkendeNaamBetrokkene: '0a3a0ffb-dc03-4aae-b207-0ed1502e60da',
		roltype: 'http://example.com/roltypen/adviseur',
		roltoelichting: 'Adviseur op dossier ZAAK-2024-3',
		registratiedatum: '2019-08-24T00:00:00.000Z',
		indicatieMachtiging: 'gemachtigde',
		contactpersoonRol: {
			emailadres: 'test@gmail.com',
			functie: 'something',
			telefoonnummer: '06 123456789',
			naam: 'Henry',
		},
		statussen: ['http://example.com/statussen/active', 'http://example.com/statussen/nieuw'],
		_expand: {},
		betrokkeneIdentificatie: {
			identificatie: '',
			achternaam: '',
			voorletters: '',
			voorvoegselAchternaam: '',
		},
	},
]

export const mockRol = (data: TRol[] = mockRolData()): TRol[] => data.map(item => new Rol(item))
