import { Rol } from './rol'
import { mockRolData } from './rol.mock'

describe('Rol Entity', () => {
	it('should create a Rol entity with full data', () => {
		const rol = new Rol(mockRolData()[0])

		expect(rol).toBeInstanceOf(Rol)
		expect(rol).toEqual(mockRolData()[0])
		expect(rol.validate().success).toBe(true)
	})
})
