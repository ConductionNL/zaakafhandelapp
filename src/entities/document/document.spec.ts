import { Document } from './document'
import { mockDocumentData } from './document.mock'

describe('Document Entity', () => {
	it('should create a Document entity with full data', () => {
		const document = new Document(mockDocumentData()[0])

		expect(document).toBeInstanceOf(Document)
		expect(document).toEqual(mockDocumentData()[0])
		expect(document.validate().success).toBe(true)
	})
})
