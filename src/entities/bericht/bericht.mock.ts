import { Bericht } from './bericht'
import { TBericht } from './bericht.types'

export const mockBerichtData = (): TBericht[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		batchID: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		aanmaakDatum: '2024-01-01',
		berichtLeverancierID: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		berichtID: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		berichtType: 'Type3',
		publicatieDatum: '2024-01-01',
		onderwerp: 'Onderwerp 3',
		berichttekst: 'Dit is de tekst van bericht 3.',
		referentie: 'Ref3',
		gebruikerID: '765432109',
		soortGebruiker: 'Burger',
		inhoud: 'VGhpcyBpcyB5ZXQgYW5vdGhlciB0ZXN0IHBkZiBmaWxlLg==',
		bijlageType: 'Pdf',
		omschrijving: 'Omschrijving voor bijlage 3',
		volgorde: '3',
	},
]

export const mockBericht = (data: TBericht[] = mockBerichtData()): TBericht[] => data.map(item => new Bericht(item))
