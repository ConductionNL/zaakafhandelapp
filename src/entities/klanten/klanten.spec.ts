import { Klant } from './klanten'
import { mockKlantData } from './klanten.mock'

describe('Klant Entity', () => {
	it('should create a Klant entity with full data', () => {
		const klant = new Klant(mockKlantData()[0])

		expect(klant).toBeInstanceOf(Klant)
		expect(klant).toEqual(mockKlantData()[0])
		expect(klant.validate().success).toBe(true)
	})

	it('should parse the contactsUid addressbook link', () => {
		const klant = new Klant({ ...mockKlantData()[0], contactsUid: 'uid-123' })

		expect(klant.contactsUid).toBe('uid-123')
		expect(klant.validate().success).toBe(true)
	})

	it('should default contactsUid to an empty string when absent', () => {
		const klant = new Klant({
			...mockKlantData()[0],
			contactsUid: undefined as unknown as string,
		})

		expect(klant.contactsUid).toBe('')
	})
})
