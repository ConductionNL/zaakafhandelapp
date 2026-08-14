import type { TBesluit } from './besluit.types'

import { Besluit } from './besluit'

/**
 *
 */
export function mockBesluitData(): TBesluit[] {
	return [
		{
			id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
			url: 'https://api.example.com/besluiten/15551d6f-44e3-43f3-a9d2-59e583c91eb0',
			besluit: 'dsadsadasdasdadfas',
			zaak: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		},
	]
}

/**
 *
 * @param data
 */
export function mockBesluit(data: TBesluit[] = mockBesluitData()): TBesluit[] {
	return data.map((item) => new Besluit(item))
}
