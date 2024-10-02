import { Zaak } from './zaak'
import { mockZaakData } from './zaak.mock'

describe('Zaak Entity', () => {
	it('should create a Zaak entity with full data', () => {
		const zaak = new Zaak(mockZaakData()[0])

		expect(zaak).toBeInstanceOf(Zaak)
		expect(zaak).toEqual(mockZaakData()[0])
		expect(zaak.validate().success).toBe(true)
	})
})
