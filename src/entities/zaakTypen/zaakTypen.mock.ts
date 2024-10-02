import { ZaakType } from './zaakTypen'
import { TZaakType } from './zaakTypen.types'

export const mockZaakTypeData = (): TZaakType[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		name: 'Zaak 3',
		summary: 'fsdfshgfhgf',
	},
]

export const mockZaakType = (data: TZaakType[] = mockZaakTypeData()): TZaakType[] => data.map(item => new ZaakType(item))
