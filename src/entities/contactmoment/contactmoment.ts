import { SafeParseReturnType, z } from 'zod'
import { TContactMoment } from './contactmoment.types'

export class ContactMoment implements TContactMoment {

	public id: string
	public uuid: string
	public notitie: string
	public klant: string
	public zaak: string
	public taak: string
	public product: string
	public startDate: string
	public status: string
	public contactmoment: string
	public medewerker: string
	public kanaal: string

	constructor(source: TContactMoment) {
		this.id = source.id || ''
		this.uuid = source.uuid || ''
		this.notitie = source.notitie || ''
		this.klant = source.klant || ''
		this.zaak = source.zaak || ''
		this.taak = source.taak || ''
		this.product = source.product || ''
		this.startDate = source.startDate || ''
		this.status = source.status || 'open'
		this.contactmoment = source.contactmoment || ''
		this.medewerker = source.medewerker || ''
		this.kanaal = source.kanaal || ''
	}

	public validate(): SafeParseReturnType<TContactMoment, unknown> {
		const schema = z.object({
			id: z.string().optional(),
			uuid: z.string().optional(),
			notitie: z.string().min(1),
			klant: z.string().min(1),
			zaak: z.string().min(1),
			taak: z.string().min(1),
			product: z.string().min(1),
			startDate: z.string().min(1),
			status: z.string().min(1),
			contactmoment: z.string().min(1),
			medewerker: z.string().min(1),
			kanaal: z.string().min(1),
		})

		return schema.safeParse(this)
	}

}
