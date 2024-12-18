import { Medewerker } from './medewerkers'
import { TMedewerker } from './medewerkers.types'

export const mockMedewerkerData = (): TMedewerker[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		voornaam: 'John',
		tussenvoegsel: 'de',
		achternaam: 'Doe',
		email: 'john.doe@example.com',
		telefoonnummer: '0612345678',
	},
]

export const mockMedewerker = (data: TMedewerker[] = mockMedewerkerData()): TMedewerker[] => data.map(item => new Medewerker(item))
