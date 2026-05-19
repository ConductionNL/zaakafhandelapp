import { ContactMoment } from './contactmoment'
import { mockContactMomentData } from './contactmoment.mock'

describe('ContactMoment Entity', () => {
	it('should create a ContactMoment entity with full data', () => {
		// mockContactMomentData() generates `startDate: new Date().toISOString()`, so
		// build the entity from a single snapshot rather than calling it twice (which
		// would yield two timestamps ~1ms apart and a spurious mismatch).
		const data = mockContactMomentData()[0]
		const contactMoment = new ContactMoment(data)

		expect(contactMoment).toBeInstanceOf(ContactMoment)
		expect(contactMoment).toEqual(data)
		expect(contactMoment.validate().success).toBe(true)
	})
})
