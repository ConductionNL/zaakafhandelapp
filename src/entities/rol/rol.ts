import { SafeParseReturnType, z } from 'zod'
import { TRol } from './rol.types'

export class Rol implements TRol {

	public id: string
	public url: string
	public uuid: string
	public zaak: string
	public betrokkene?: string
	public betrokkeneType: 'natuurlijk_persoon' | 'niet_natuurlijk_persoon' | 'vestiging' | 'organisatorische_eenheid' | 'medewerker'
	public afwijkendeNaamBetrokkene?: string
	public roltype: string
	public omschrijving: string
	public omschrijvingGeneriek: 'adviseur' | 'behandelaar' | 'belanghebbende' | 'beslisser' | 'initiator' | 'klantcontacter' | 'zaakcoordinator' | 'mede_initiator'
	public roltoelichting: string
	public registratiedatum: string
	public indicatieMachtiging?: 'gemachtigde' | 'machtiginggever'
	public contactpersoonRol?: {
        emailadres?: string
        functie?: string
        telefoonnummer?: string
        naam: string
    }

	public statussen: string[]
	public _expand: {
        zaak?: string
        roltype?: string
        statussen?: string
    }

	public betrokkeneIdentificatie?: {
        identificatie?: string
        achternaam?: string
        voorletters?: string
        voorvoegselAchternaam?: string
    }

	constructor(source: TRol) {
		this.id = source.id || null
		this.uuid = source.uuid || ''
		this.omschrijving = source.omschrijving || ''
		this.omschrijvingGeneriek = source.omschrijvingGeneriek || 'adviseur'
		this.url = source.url || ''
		this.zaak = source.zaak || ''
		this.betrokkene = source.betrokkene || ''
		this.betrokkeneType = source.betrokkeneType || 'natuurlijk_persoon'
		this.afwijkendeNaamBetrokkene = source.afwijkendeNaamBetrokkene || ''
		this.roltype = source.roltype || ''
		this.roltoelichting = source.roltoelichting || ''
		this.registratiedatum = source.registratiedatum || ''
		this.indicatieMachtiging = source.indicatieMachtiging || 'gemachtigde'
		this.contactpersoonRol = source.contactpersoonRol || null
		this.statussen = source.statussen || []
		this._expand = source._expand || {}
		this.betrokkeneIdentificatie = source.betrokkeneIdentificatie || {}
	}

	public validate(): SafeParseReturnType<TRol, unknown> {
		const schema = z.object({
			id: z.string(),
			url: z.string().url().min(1).max(1000),
			uuid: z.string().uuid(),
			zaak: z.string().url().min(1).max(1000),
			betrokkene: z.string().url().max(1000).optional(),
			betrokkeneType: z.enum(['natuurlijk_persoon', 'niet_natuurlijk_persoon', 'vestiging', 'organisatorische_eenheid', 'medewerker']),
			afwijkendeNaamBetrokkene: z.string().max(625).optional(),
			roltype: z.string().url().max(1000),
			omschrijving: z.string(),
			omschrijvingGeneriek: z.enum(['adviseur', 'behandelaar', 'belanghebbende', 'beslisser', 'initiator', 'klantcontacter', 'zaakcoordinator', 'mede_initiator']),
			roltoelichting: z.string().max(1000),
			registratiedatum: z.string().datetime(),
			indicatieMachtiging: z.enum(['gemachtigde', 'machtiginggever', '']).optional(),
			contactpersoonRol: z.object({
				emailadres: z.string().max(254).optional(),
				functie: z.string().max(50).optional(),
				telefoonnummer: z.string().max(20).optional(),
				naam: z.string().max(40),
			}).optional(),
			statussen: z.array(z.string().url().min(1).max(1000)).superRefine((val, ctx) => {
				// statussen is supposed to be a unique array according to docs
				// https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/redoc-1.5.1#tag/rollen/operation/rol_retrieve
				if (val.length !== new Set(val).size) {
					ctx.addIssue({
						code: z.ZodIssueCode.custom,
						message: 'No duplicates allowed.',
					})
				}
			}),
			_expand: z.object({
				zaak: z.record(z.any()),
				roltype: z.record(z.any()),
				statussen: z.record(z.any()),
			}),
			betrokkeneIdentificatie: z.object({
				identificatie: z.string().optional(),
				achternaam: z.string().optional(),
				voorletters: z.string().optional(),
				voorvoegselAchternaam: z.string().optional(),
			}).optional(),
		})

		return schema.safeParse(this)
	}

}
