import { SafeParseReturnType, z } from 'zod'
import { TRol } from './rol.types'

export class Rol implements TRol {

	public id: string
	public uuid: string
	public omschrijving: string
	public omschrijvingGeneriek: string
	public url: string
	public zaak: string
	public betrokkene: string
	public betrokkeneType: string
	public afwijkendeNaamBetrokkene: string
	public roltype: string
	public roltoelichting: string
	public registratiedatum: string
	public indicatieMachtiging: string
	public contactpersoonRol: {
        emailadres?: string
        functie?: string
        telefoonnummer?: string
        naam?: string
    }

	public statussen: string[]
	public _expand: {
        zaak?: string
        roltype?: string
        statussen?: string
    }

	public betrokkeneIdentificatie: {
        identificatie?: string
        achternaam?: string
        voorletters?: string
        voorvoegselAchternaam?: string
    }

	constructor(source: TRol) {
		this.id = source.id || null
		this.uuid = source.uuid || ''
		this.omschrijving = source.omschrijving || ''
		this.omschrijvingGeneriek = source.omschrijvingGeneriek || ''
		this.url = source.url || ''
		this.zaak = source.zaak || ''
		this.betrokkene = source.betrokkene || ''
		this.betrokkeneType = source.betrokkeneType || ''
		this.afwijkendeNaamBetrokkene = source.afwijkendeNaamBetrokkene || ''
		this.roltype = source.roltype || ''
		this.roltoelichting = source.roltoelichting || ''
		this.registratiedatum = source.registratiedatum || ''
		this.indicatieMachtiging = source.indicatieMachtiging || ''
		this.contactpersoonRol = source.contactpersoonRol || {}
		this.statussen = source.statussen || []
		this._expand = source._expand || {}
		this.betrokkeneIdentificatie = source.betrokkeneIdentificatie || {}
	}

	public validate(): SafeParseReturnType<TRol, unknown> {
		const schema = z.object({
			id: z.string().optional(),
			uuid: z.string().optional(),
			omschrijving: z.string().min(1),
			omschrijvingGeneriek: z.string(),
			url: z.string().url(),
			zaak: z.string(),
			betrokkene: z.string(),
			betrokkeneType: z.string(),
			afwijkendeNaamBetrokkene: z.string(),
			roltype: z.string(),
			roltoelichting: z.string(),
			registratiedatum: z.string(),
			indicatieMachtiging: z.string(),
			contactpersoonRol: z.object({
				emailadres: z.string(),
				functie: z.string(),
				telefoonnummer: z.string(),
				naam: z.string(),
			}),
			statussen: z.string().array(),
			_expand: z.object({
				zaak: z.string(),
				roltype: z.string(),
				statussen: z.string(),
			}),
			betrokkeneIdentificatie: z.object({
				identificatie: z.string(),
				achternaam: z.string(),
				voorletters: z.string(),
				voorvoegselAchternaam: z.string(),
			}),
		})

		return schema.safeParse(this)
	}

}
