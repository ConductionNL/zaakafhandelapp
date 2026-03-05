import { Document } from './document'
import { TDocument } from './document.types'

export const mockDocumentData = (): TDocument[] => [
	{
		id: '15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		zaak: '550e8400-e29b-41d4-a716-446655440000',
		url: 'https://example.com/documents/15551d6f-44e3-43f3-a9d2-59e583c91eb0',
		identificatie: 'DOC-2023-001',
		bronorganisatie: '123456789',
		creatiedatum: '2023-01-01T00:00:00Z',
		titel: 'Example Document',
		vertrouwelijkheidaanduiding: 'openbaar',
		auteur: 'John Doe',
		status: 'definitief',
		inhoudIsVervallen: false,
		formaat: 'application/pdf',
		taal: 'nld',
		versie: 1,
		beginRegistratie: '2023-01-01T00:00:00Z',
		bestandsnaam: 'example.pdf',
		inhoud: 'https://example.com/documents/15551d6f-44e3-43f3-a9d2-59e583c91eb0/download',
		bestandsomvang: BigInt(1024),
		link: 'https://example.com/viewer',
		beschrijving: 'An example document',
		indicatieGebruiksrecht: true,
		verschijningsvorm: 'digitaal',
		ondertekening: {
			soort: 'digitaal',
			datum: '2023-01-01T00:00:00Z',
		},
		integriteit: {
			algoritme: 'sha_256',
			waarde: 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
			datum: '2023-01-01T00:00:00Z',
		},
		informatieobjecttype: 'https://example.com/catalogi/informatieobjecttypen/1',
		locked: false,
		bestandsdelen: [
			{
				url: 'https://example.com/documents/15551d6f-44e3-43f3-a9d2-59e583c91eb0/parts/1',
				volgnummer: 1,
				omvang: 1024,
				voltooid: true,
				lock: '',
			},
		],
		trefwoorden: ['example', 'test'],
		_expand: {
			informatieobjecttype: {
				url: 'https://example.com/catalogi/informatieobjecttypen/1',
				catalogus: 'https://example.com/catalogi/1',
				omschrijving: 'Example Document Type',
				vertrouwelijkheidaanduiding: 'openbaar',
				beginGeldigheid: '2023-01-01',
				eindeGeldigheid: null,
				beginObject: null,
				eindeObject: null,
				concept: false,
				zaaktypen: 'https://example.com/catalogi/zaaktypen/1',
				besluittypen: ['https://example.com/catalogi/besluittypen/1'],
				informatieobjectcategorie: 'example',
				trefwoorden: ['example'],
				omschrijvingGeneriek: {
					informatieobjecttypeOmschrijvingGeneriek: 'Example Generic Description',
					definitieInformatieobjecttypeOmschrijvingGeneriek: 'An example document type',
					herkomstInformatieobjecttypeOmschrijvingGeneriek: 'KING',
					hierarchieInformatieobjecttypeOmschrijvingGeneriek: 'parent',
					opmerkingInformatieobjecttypeOmschrijvingGeneriek: null,
				},
			},
		},
	},
]

export const mockDocument = (data: TDocument[] = mockDocumentData()): TDocument[] => data.map(item => new Document(item))
