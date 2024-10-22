import { SafeParseReturnType, z } from 'zod'
import { TBericht, BerichtID } from './bericht.types'

export class Bericht implements TBericht {

	public id: string
	public batchID: string
	public aanmaakDatum: string
	public berichtLeverancierID: string
	public berichtID: BerichtID
	public berichtType: string
	public publicatieDatum: string
	public onderwerp: string
	public berichttekst: string
	public referentie: string
	public gebruikerID: string
	public soortGebruiker: string
	public inhoud: string
	public bijlageType: string
	public omschrijving: string
	public volgorde: string

	constructor(source: TBericht) {
		this.id = source.id || ''
		this.batchID = source.batchID || ''
		this.aanmaakDatum = source.aanmaakDatum || ''
		this.berichtLeverancierID = source.berichtLeverancierID || ''
		this.berichtID = source.berichtID || ''
		this.berichtType = source.berichtType || ''
		this.publicatieDatum = source.publicatieDatum || ''
		this.onderwerp = source.onderwerp || ''
		this.berichttekst = source.berichttekst || ''
		this.referentie = source.referentie || ''
		this.gebruikerID = source.gebruikerID || ''
		this.soortGebruiker = source.soortGebruiker || ''
		this.inhoud = source.inhoud || ''
		this.bijlageType = source.bijlageType || ''
		this.omschrijving = source.omschrijving || ''
		this.volgorde = source.volgorde || ''
	}

	public validate(): SafeParseReturnType<TBericht, unknown> {
		const schema = z.object({
			id: z.string(),
			batchID: z.string(),
			aanmaakDatum: z.string(),
			berichtLeverancierID: z.string(),
			berichtID: z.string(),
			berichtType: z.string(),
			publicatieDatum: z.string(),
			onderwerp: z.string(),
			berichttekst: z.string(),
			referentie: z.string(),
			gebruikerID: z.string(),
			soortGebruiker: z.string(),
			inhoud: z.string(),
			bijlageType: z.string(),
			omschrijving: z.string(),
			volgorde: z.string(),
		})

		return schema.safeParse(this)
	}

}
