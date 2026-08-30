import type { SafeParseReturnType } from 'zod'
import type { TResultaat } from './resultaat.types'

import { z } from 'zod'

export class Resultaat implements TResultaat {
	public id: string
	public url: string
	public zaak: string
	public resultaattype: string
	public toelichting: string
	/**
	 * @spec openspec/specs/domain-entities/spec.md#REQ-001
	 */

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
