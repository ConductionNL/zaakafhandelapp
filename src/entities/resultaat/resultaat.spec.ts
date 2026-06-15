import { Resultaat } from './resultaat'
import { mockResultaatData } from './resultaat.mock'

describe('Resultaat Entity', () => {
	it('should create a Resultaat entity with full data', () => {
		const resultaat = new Resultaat(mockResultaatData()[0])

		expect(resultaat).toBeInstanceOf(Resultaat)
		expect(resultaat).toEqual(mockResultaatData()[0])
		expect(resultaat.validate().success).toBe(true)
	})
})
