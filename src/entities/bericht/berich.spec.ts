import { Bericht } from './bericht'
import { mockBerichtData } from './bericht.mock'

describe('Bericht Entity', () => {
	it('should create a Bericht entity with full data', () => {
		const bericht = new Bericht(mockBerichtData()[0])

		expect(bericht).toBeInstanceOf(Bericht)
		expect(bericht).toEqual(mockBerichtData()[0])
		expect(bericht.validate().success).toBe(true)
	})
})
