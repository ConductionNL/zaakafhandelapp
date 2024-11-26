import { Klant } from './klanten'
import { TKlant } from './klanten.types'

export const mockKlantData = (): TKlant[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		type: 'persoon',
		voornaam: 'John',
		tussenvoegsel: 'de',
		achternaam: 'Doe',
		bsn: '1234567890',
		geboortedatum: '1990-01-01',
		land: 'Nederland',
		telefoonnummer: '0612345678',
		emailadres: 'john.doe@example.com',
		straatnaam: 'Example Street',
		plaats: 'Example City',
		postcode: '1234AB',
		huisnummer: '123',
		functie: 'Software Developer',
		aanmaakkanaal: 'email',
		bronorganisatie: 'Example Corp',
		kvkNummer: '1234567890',
		bedrijfsnaam: 'Example Corp',
		websiteUrl: 'http://example.com',
		url: 'http://example.com',
		geverifieerd: 'true',
		subject: 'John Doe',
		subjectIdentificatie: '1234567890',
		subjectType: 'person',
	},
]

export const mockKlant = (data: TKlant[] = mockKlantData()): TKlant[] => data.map(item => new Klant(item))
