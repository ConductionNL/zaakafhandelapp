import { SafeParseReturnType, z } from 'zod'
import { TBesluit } from './besluit.types'

export class Besluit implements TBesluit {

	public id: string
	public url: string
	public besluit: string
	public zaak: string

	constructor(source: TBesluit) {
		this.id = source.id || ''
		this.url = source.url || ''
		this.besluit = source.besluit || ''
		this.zaak = source.zaak || ''
	}

	public validate(): SafeParseReturnType<TBesluit, unknown> {
		const schema = z.object({
			id: z.string(),
			url: z.string(),
			besluit: z.string(),
			zaak: z.string().min(1, 'Zaak is verplicht'),
		})

		return schema.safeParse(this)
	}

}
