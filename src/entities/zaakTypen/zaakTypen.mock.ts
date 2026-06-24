import { ZaakType } from './zaakTypen'
import { TZaakType } from './zaakTypen.types'

export const mockZaakTypeData = (): TZaakType[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		identificatie: 'Zaak 3',
		omschrijving: 'fsdfshgfhgf',
		omschrijvingGeneriek: 'fsdfshgfhgf',
		vertrouwelijkheidaanduiding: 'fsdfshgfhgf',
		doel: 'fsdfshgfhgf',
		aanleiding: 'fsdfshgfhgf',
		toelichting: 'fsdfshgfhgf',
		indicatieInternOfExtern: 'fsdfshgfhgf',
		handelingInitiator: 'fsdfshgfhgf',
		onderwerp: 'fsdfshgfhgf',
		handelingBehandelaar: 'fsdfshgfhgf',
		doorlooptijd: 'fsdfshgfhgf',
		servicenorm: 'fsdfshgfhgf',
		opschortingEnAanhoudingMogelijk: 'fsdfshgfhgf',
		verlengingMogelijk: 'fsdfshgfhgf',
		verlengingstermijn: 'fsdfshgfhgf',
		publicatieIndicatie: 'fsdfshgfhgf',
		publicatietekst: 'fsdfshgfhgf',
		productenOfDiensten: 'fsdfshgfhgf',
		selectielijstProcestype: 'fsdfshgfhgf',
		referentieproces: 'fsdfshgfhgf',
		catalogus: 'fsdfshgfhgf',
		beginGeldigheid: 'fsdfshgfhgf',
		eindeGeldigheid: 'fsdfshgfhgf',
		beginObject: 'fsdfshgfhgf',
		eindeObject: 'fsdfshgfhgf',
		versiedatum: 'fsdfshgfhgf',
	},
]

export const mockZaakType = (data: TZaakType[] = mockZaakTypeData()): TZaakType[] => data.map(item => new ZaakType(item))
