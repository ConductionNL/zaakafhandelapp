import { Klant } from './klanten'
import { mockKlantData } from './klanten.mock'

describe('Klant Entity', () => {
	it('should create a Klant entity with full data', () => {
		const klant = new Klant(mockKlantData()[0])

		expect(klant).toBeInstanceOf(Klant)
		expect(klant).toEqual(mockKlantData()[0])
		expect(klant.validate().success).toBe(true)
	})
})
