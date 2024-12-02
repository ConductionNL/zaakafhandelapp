import { ContactMoment } from './contactmoment'
import { mockContactMomentData } from './contactmoment.mock'

describe('ContactMoment Entity', () => {
	it('should create a ContactMoment entity with full data', () => {
		const contactMoment = new ContactMoment(mockContactMomentData()[0])

		expect(contactMoment).toBeInstanceOf(ContactMoment)
		expect(contactMoment).toEqual(mockContactMomentData()[0])
		expect(contactMoment.validate().success).toBe(true)
	})
})
