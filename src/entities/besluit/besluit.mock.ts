import { Besluit } from './besluit'
import { TBesluit } from './besluit.types'

export const mockBesluitData = (): TBesluit[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		url: 'https://api.example.com/besluiten/15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		besluit: 'dsadsadasdasdadfas',
	},
]

export const mockBesluit = (data: TBesluit[] = mockBesluitData()): TBesluit[] => data.map(item => new Besluit(item))
