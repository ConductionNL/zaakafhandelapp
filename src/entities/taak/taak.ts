import { SafeParseReturnType, z } from 'zod'
import { TTaak, ZaakID } from './taak.types'

export class Taak implements TTaak {

	public id: string
	public title: string
	public zaak: ZaakID
	public type: string
	public status: string
	public onderwerp: string
	public toelichting: string
	public actie: string
	public klant: string

	constructor(source: TTaak) {
		this.id = source.id || ''
		this.title = source.title || ''
		this.zaak = source.zaak || ''
		this.type = source.type || ''
		this.status = source.status || ''
		this.onderwerp = source.onderwerp || ''
		this.toelichting = source.toelichting || ''
		this.actie = source.actie || ''
		this.klant = source.klant || ''
	}

	public validate(): SafeParseReturnType<TTaak, unknown> {
		const schema = z.object({
			id: z.string().min(1),
			title: z.string().min(1),
			zaak: z.string().min(1),
			type: z.string().min(1),
			status: z.string().min(1),
			onderwerp: z.string().min(1),
			toelichting: z.string(),
			actie: z.string(),
			klant: z.string(),
		})

		return schema.safeParse(this)
	}

}
