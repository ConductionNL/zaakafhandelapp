import { SafeParseReturnType, z } from 'zod'
import { TResultaat } from './resultaat.types'

export class Resultaat implements TResultaat {

	public id: string
	public url: string
	public zaak: string
	public resultaattype: string
	public toelichting: string

	constructor(source: TResultaat) {
		this.id = source.id || ''
		this.url = source.url || ''
		this.zaak = source.zaak || ''
		this.resultaattype = source.resultaattype || ''
		this.toelichting = source.toelichting || ''
	}

	public validate(): SafeParseReturnType<TResultaat, unknown> {
		const schema = z.object({
			id: z.string(),
			url: z.string(),
			zaak: z.string(),
			resultaattype: z.string(),
			toelichting: z.string(),
		})

		return schema.safeParse(this)
	}

}
