import { Taak } from './taak'
import { TTaak } from './taak.types'

export const mockTaakData = (): TTaak[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		title: 'Interne Audit',
		zaak: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		type: 'Audit',
		status: 'verwerkt',
		onderwerp: 'Uitvoering van een interne audit om de naleving van kwaliteitsnormen te controleren.',
		toelichting: 'Deze taak omvat het uitvoeren van een gedetailleerde interne audit van de bedrijfsprocessen om te controleren of alle afdelingen voldoen aan de vastgestelde kwaliteitsnormen. De bevindingen worden gedocumenteerd en er worden aanbevelingen gedaan voor verbeteringen.',
		actie: 'Voorbereiden van auditchecklist, uitvoeren van audits, rapporteren van bevindingen, aanbevelen van verbeteringen.',
	},
]

export const mockTaak = (data: TTaak[] = mockTaakData()): TTaak[] => data.map(item => new Taak(item))
