import { SafeParseReturnType, z } from 'zod'
import { TTaak, ZaakID } from './taak.types'
import getValidISOstring from '../../services/getValidISOstring'

export class Taak implements TTaak {

	public id: string
	public title: string
	public zaak: ZaakID
	public type: string
	public status: string
	public deadline: string
	public onderwerp: string
	public toelichting: string
	public actie: string
	public klant: string
	public contactmoment: string
	public medewerker: string
	constructor(source: TTaak) {
		this.id = source.id || ''
		this.title = source.title || ''
		this.zaak = source.zaak || ''
		this.type = source.type || ''
		this.status = source.status || ''
		this.deadline = source.deadline ? getValidISOstring(source.deadline) : null
		this.onderwerp = source.onderwerp || ''
		this.toelichting = source.toelichting || ''
		this.actie = source.actie || ''
		this.klant = source.klant || ''
		this.medewerker = source.medewerker || ''
		this.contactmoment = source.contactmoment || ''
	}

	public validate(): SafeParseReturnType<TTaak, unknown> {
		const schema = z.object({
			id: z.string().min(1),
			title: z.string().min(1),
			zaak: z.string().min(1),
			type: z.string().min(1),
			status: z.string().min(1),
			deadline: z.string().datetime().nullable(),
			onderwerp: z.string().min(1),
			toelichting: z.string(),
			actie: z.string(),
			klant: z.string(),
			medewerker: z.string(),
			contactmoment: z.string().nullable(),
		})

		return schema.safeParse(this)
	}

}
