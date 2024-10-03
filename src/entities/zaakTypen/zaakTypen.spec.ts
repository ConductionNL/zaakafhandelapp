import { ZaakType } from './zaakTypen'
import { mockZaakTypeData } from './zaakTypen.mock'

describe('ZaakType Entity', () => {
	it('should create a ZaakType entity with full data', () => {
		const zaakType = new ZaakType(mockZaakTypeData()[0])

		expect(zaakType).toBeInstanceOf(ZaakType)
		expect(zaakType).toEqual(mockZaakTypeData()[0])
		expect(zaakType.validate().success).toBe(true)
	})
})
