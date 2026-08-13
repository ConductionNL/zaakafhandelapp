import { Zaak } from './zaak'
import { mockZaakData } from './zaak.mock'

describe('Zaak Entity', () => {
	it('should create a Zaak entity with full data', () => {
		const zaak = new Zaak(mockZaakData()[0])

		expect(zaak).toBeInstanceOf(Zaak)
		expect(zaak).toEqual(mockZaakData()[0])
		expect(zaak.validate().success).toBe(true)
	})

	it('should parse the opschorting and verlenging groups', () => {
		const zaak = new Zaak({
			...mockZaakData()[0],
			opschorting: { indicatie: true, reden: 'Wacht op stukken' },
			verlenging: { reden: 'Meer tijd nodig', duur: 'P14D' },
		})

		expect(zaak.opschorting.indicatie).toBe(true)
		expect(zaak.opschorting.reden).toBe('Wacht op stukken')
		expect(zaak.verlenging.duur).toBe('P14D')
		expect(zaak.validate().success).toBe(true)
	})

	it('should default the opschorting and verlenging groups when absent', () => {
		const zaak = new Zaak({
			...mockZaakData()[0],
			opschorting: undefined as never,
			verlenging: undefined as never,
		})

		expect(zaak.opschorting).toEqual({ indicatie: false, reden: '' })
		expect(zaak.verlenging).toEqual({ reden: '', duur: '' })
	})
})
