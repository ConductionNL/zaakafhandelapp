import { Taak } from './taak'
import { mockTaakData } from './taak.mock'

describe('Taak Entity', () => {
	it('should create a Taak entity with full data', () => {
		const taak = new Taak(mockTaakData()[0])

		expect(taak).toBeInstanceOf(Taak)
		expect(taak).toEqual(mockTaakData()[0])
		expect(taak.validate().success).toBe(true)
	})
})
