import type { TMedewerker } from './medewerkers.types'

import { Medewerker } from './medewerkers'

/**
 *
 */
export function mockMedewerkerData(): TMedewerker[] {
	return [
		{
			id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
			voornaam: 'John',
			tussenvoegsel: 'de',
			achternaam: 'Doe',
			email: 'john.doe@example.com',
			telefoonnummer: '0612345678',
		},
	]
}

/**
 *
 * @param data
 */
export function mockMedewerker(
	data: TMedewerker[] = mockMedewerkerData(),
): TMedewerker[] {
	return data.map((item) => new Medewerker(item))
}
