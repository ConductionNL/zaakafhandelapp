import { Medewerker } from './medewerkers'
import { mockMedewerkerData } from './medewerkers.mock'

describe('Medewerker Entity', () => {
	it('should create a Medewerker entity with full data', () => {
		const medewerker = new Medewerker(mockMedewerkerData()[0])

		expect(medewerker).toBeInstanceOf(Medewerker)
		expect(medewerker).toEqual(mockMedewerkerData()[0])
		expect(medewerker.validate().success).toBe(true)
	})
})
