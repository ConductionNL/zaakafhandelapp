import { SafeParseReturnType, z } from 'zod'
import { TKlant, TKlantType } from './klanten.types'

export class Klant implements TKlant {

	public id: string
	public type: TKlantType

	public voornaam: string
	public tussenvoegsel: string
	public achternaam: string
	public bsn: string
	public geboortedatum: string
	public geslacht: "man" | "vrouw" | "overige"
	public land: string

	public telefoonnummer: string
	public emailadres: string

	public straatnaam: string
	public plaats: string
	public postcode: string
	public huisnummer: string

	public functie: string
	public aanmaakkanaal: string
	public bronorganisatie: string
	public bedrijfsnaam: string
	public kvkNummer: string
	public websiteUrl: string
	public url: string
	public geverifieerd: string
	public subject: string
	public subjectIdentificatie: string
	public subjectType: string

	constructor(source: TKlant) {
		this.id = source.id || ''
		this.type = source.type || 'persoon'
		this.voornaam = source.voornaam || ''
		this.tussenvoegsel = source.tussenvoegsel || ''
		this.achternaam = source.achternaam || ''
		this.bsn = source.bsn || ''
		this.geboortedatum = source.geboortedatum || ''
		this.geslacht = source.geslacht || null
		this.land = source.land || ''
		this.telefoonnummer = source.telefoonnummer || ''
		this.emailadres = source.emailadres || ''
		this.straatnaam = source.straatnaam || ''
		this.plaats = source.plaats || ''
		this.postcode = source.postcode || ''
		this.huisnummer = source.huisnummer || ''
		this.functie = source.functie || ''
		this.aanmaakkanaal = source.aanmaakkanaal || ''
		this.bronorganisatie = source.bronorganisatie || ''
		this.bedrijfsnaam = source.bedrijfsnaam || ''
		this.kvkNummer = source.kvkNummer || ''
		this.websiteUrl = source.websiteUrl || ''
		this.url = source.url || ''
		this.geverifieerd = source.geverifieerd || ''
		this.subject = source.subject || ''
		this.subjectIdentificatie = source.subjectIdentificatie || ''
		this.subjectType = source.subjectType || ''
	}

	public validate(): SafeParseReturnType<TKlant, unknown> {
		const schema = z.object({
			id: z.string().optional(),
			voornaam: z.string().min(1),
			tussenvoegsel: z.string(),
			achternaam: z.string(),
			bsn: z.string(),
			geboortedatum: z.string(),
			geslacht: z.enum(["man", "vrouw", "overige"]).or(z.null()),
			land: z.string(),
			telefoonnummer: z.string(),
			emailadres: z.string().email(),
			straatnaam: z.string(),
			plaats: z.string(),
			postcode: z.string(),
			huisnummer: z.string(),
			functie: z.string(),
			aanmaakkanaal: z.string(),
			bronorganisatie: z.string(),
			bedrijfsnaam: z.string(),
			kvkNummer: z.string(),
			websiteUrl: z.string().url(),
			url: z.string().url(),
			geverifieerd: z.string(),
			subject: z.string(),
			subjectIdentificatie: z.string(),
			subjectType: z.string(),
		})

		return schema.safeParse(this)
	}

}
