import { Besluit } from './besluit'
import { mockBesluitData } from './besluit.mock'

describe('Besluit Entity', () => {
	it('should create a Besluit entity with full data', () => {
		const besluit = new Besluit(mockBesluitData()[0])

		expect(besluit).toBeInstanceOf(Besluit)
		expect(besluit).toEqual(mockBesluitData()[0])
		expect(besluit.validate().success).toBe(true)
	})
})
