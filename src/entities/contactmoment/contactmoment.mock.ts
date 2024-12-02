import { ContactMoment } from './contactmoment'
import { TContactMoment } from './contactmoment.types'

export const mockContactMomentData = (): TContactMoment[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		uuid: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		notitie: 'Zaak 3',
		klant: 'Klant 3',
		zaak: 'Zaak 3',
		taak: 'Taak 3',
		product: 'Product 3',
		startDate: new Date().toISOString(),
		status: 'open',
	},
]

export const mockContactMoment = (data: TContactMoment[] = mockContactMomentData()): TContactMoment[] => data.map(item => new ContactMoment(item))
