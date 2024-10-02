import { SafeParseReturnType, z } from 'zod'
import { TZaakType } from './zaakTypen.types'

export class ZaakType implements TZaakType {

	public id: string
	public name: string
	public summary: string

	constructor(source: TZaakType) {
		this.id = source.id || ''
		this.name = source.name || ''
		this.summary = source.summary || ''
	}

	public validate(): SafeParseReturnType<TZaakType, unknown> {
		const schema = z.object({
			id: z.string().optional(),
			name: z.string().min(1),
			summary: z.string().min(1),
		})

		return schema.safeParse(this)
	}

}
