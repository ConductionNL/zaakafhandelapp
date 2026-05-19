import { Document } from './document'
import { mockDocumentData } from './document.mock'

describe('Document Entity', () => {
	it('should create a Document entity with full data', () => {
		const data = mockDocumentData()[0]
		const document = new Document(data)

		expect(document).toBeInstanceOf(Document)
		// The Document entity is a superset of the mock payload (it back-fills
		// optional fields like ontvangstdatum/_expand with null), so assert
		// containment rather than strict equality.
		expect(document).toEqual(expect.objectContaining(data))
		expect(document.validate().success).toBe(true)
	})
})
