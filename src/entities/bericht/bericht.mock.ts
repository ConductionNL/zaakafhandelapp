import type { TBericht } from './bericht.types'

import { Bericht } from './bericht'

/**
 *
 */
export function mockBerichtData(): TBericht[] {
	return [
		{
			id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
			title: 'Bericht 1',
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
}

/**
 *
 * @param data
 */
export function mockBericht(data: TBericht[] = mockBerichtData()): TBericht[] {
	return data.map((item) => new Bericht(item))
}
