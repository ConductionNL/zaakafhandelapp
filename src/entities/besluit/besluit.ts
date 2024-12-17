import { SafeParseReturnType, z } from 'zod'
import { TBesluit } from './besluit.types'

export class Besluit implements TBesluit {

	public id: string
	public url: string
	public besluit: string

	constructor(source: TBesluit) {
		this.id = source.id || ''
		this.url = source.url || ''
		this.besluit = source.besluit || ''
	}

	public validate(): SafeParseReturnType<TBesluit, unknown> {
		const schema = z.object({
			id: z.string(),
			url: z.string(),
			besluit: z.string(),
		})

		return schema.safeParse(this)
	}

}
