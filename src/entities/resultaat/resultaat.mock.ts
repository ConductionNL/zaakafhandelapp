import { Resultaat } from './resultaat'
import { TResultaat } from './resultaat.types'

export const mockResultaatData = (): TResultaat[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		url: 'https://api.example.com/resultaten/15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		zaak: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		resultaattype: 'Audit',
		toelichting: 'Deze taak omvat het uitvoeren van een gedetailleerde interne audit van de bedrijfsprocessen om te controleren of alle afdelingen voldoen aan de vastgestelde kwaliteitsnormen. De bevindingen worden gedocumenteerd en er worden aanbevelingen gedaan voor verbeteringen.',
	},
]

export const mockResultaat = (data: TResultaat[] = mockResultaatData()): TResultaat[] => data.map(item => new Resultaat(item))
