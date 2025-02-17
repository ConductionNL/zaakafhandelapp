import { SafeParseReturnType, z } from 'zod'
import { TDocument } from './document.types'

export class Document implements TDocument {

	public id: string
	public zaak?: string
	public url: string
	public identificatie?: string
	public bronorganisatie: string
	public creatiedatum: string
	public titel: string
	public vertrouwelijkheidaanduiding?: 'openbaar' | 'beperkt_openbaar' | 'intern' | 'zaakvertrouwelijk' | 'vertrouwelijk' | 'confidentieel' | 'geheim' | 'zeer_geheim'
	public auteur: string
	public status?: 'in_bewerking' | 'ter_vaststelling' | 'definitief' | 'gearchiveerd'
	public inhoudIsVervallen?: boolean
	public formaat?: string
	public taal: string
	public versie: number
	public beginRegistratie: string
	public bestandsnaam?: string
	public inhoud?: string
	public bestandsomvang?: bigint
	public link?: string
	public beschrijving?: string
	public ontvangstdatum?: string
	public verzenddatum?: string
	public indicatieGebruiksrecht?: boolean
	public verschijningsvorm?: string
	public ondertekening?: {
		soort: 'analoog' | 'digitaal' | 'pki'
		datum: string
	}

	public integriteit?: {
		algoritme: 'crc_16' | 'crc_32' | 'crc_64' | 'fletcher_4' | 'fletcher_8' | 'fletcher_16' | 'fletcher_32' | 'hmac' | 'md5' | 'sha_1' | 'sha_256' | 'sha_512' | 'sha_3'
		waarde: string
		datum: string
	}

	public informatieobjecttype: string
	public locked: boolean
	public bestandsdelen: {
		url: string
		volgnummer: number
		omvang: number
		voltooid: boolean
		lock: string
	}[]

	public trefwoorden?: string[]
	public _expand?: {
		informatieobjecttype: {
			url: string
			catalogus: string
			omschrijving: string
			vertrouwelijkheidaanduiding: 'openbaar' | 'beperkt_openbaar' | 'intern' | 'zaakvertrouwelijk' | 'vertrouwelijk' | 'confidentieel' | 'geheim' | 'zeer_geheim'
			beginGeldigheid: string
			eindeGeldigheid?: string
			beginObject?: string
			eindeObject?: string
			concept: boolean
			zaaktypen: string
			besluittypen: string[]
			informatieobjectcategorie: string
			trefwoorden?: string[]
			omschrijvingGeneriek: {
				informatieobjecttypeOmschrijvingGeneriek: string
				definitieInformatieobjecttypeOmschrijvingGeneriek: string
				herkomstInformatieobjecttypeOmschrijvingGeneriek: string
				hierarchieInformatieobjecttypeOmschrijvingGeneriek: string
				opmerkingInformatieobjecttypeOmschrijvingGeneriek?: string
			}
		}
	}

	constructor(source: TDocument) {
		this.id = source.id || ''
		this.zaak = source.zaak || null
		this.url = source.url || ''
		this.identificatie = source.identificatie || null
		this.bronorganisatie = source.bronorganisatie || ''
		this.creatiedatum = source.creatiedatum || ''
		this.titel = source.titel || ''
		this.vertrouwelijkheidaanduiding = source.vertrouwelijkheidaanduiding || null
		this.auteur = source.auteur || ''
		this.status = source.status || null
		this.inhoudIsVervallen = source.inhoudIsVervallen || null
		this.formaat = source.formaat || null
		this.taal = source.taal || ''
		this.versie = source.versie || 0
		this.beginRegistratie = source.beginRegistratie || ''
		this.bestandsnaam = source.bestandsnaam || null
		this.inhoud = source.inhoud || null
		this.bestandsomvang = source.bestandsomvang || null
		this.link = source.link || null
		this.beschrijving = source.beschrijving || null
		this.ontvangstdatum = source.ontvangstdatum || null
		this.verzenddatum = source.verzenddatum || null
		this.indicatieGebruiksrecht = source.indicatieGebruiksrecht || null
		this.verschijningsvorm = source.verschijningsvorm || null
		this.ondertekening = source.ondertekening || null
		this.integriteit = source.integriteit || null
		this.informatieobjecttype = source.informatieobjecttype || ''
		this.locked = source.locked || false
		this.bestandsdelen = source.bestandsdelen || []
		this.trefwoorden = source.trefwoorden || null
		this._expand = source._expand || null
	}

	public validate(): SafeParseReturnType<TDocument, unknown> {
		const schema = z.object({
			id: z.string(),
			zaak: z.string().nullable(),
			url: z.string().min(1).max(1000).url(),
			identificatie: z.string().max(40).nullable(),
			bronorganisatie: z.string().max(9),
			creatiedatum: z.string(),
			titel: z.string().max(200),
			vertrouwelijkheidaanduiding: z.enum(['openbaar', 'beperkt_openbaar', 'intern', 'zaakvertrouwelijk', 'vertrouwelijk', 'confidentieel', 'geheim', 'zeer_geheim']).nullable(),
			auteur: z.string().max(200),
			status: z.enum(['in_bewerking', 'ter_vaststelling', 'definitief', 'gearchiveerd']).nullable(),
			inhoudIsVervallen: z.boolean().nullable(),
			formaat: z.string().max(255).nullable(),
			taal: z.string().length(3),
			versie: z.number(),
			beginRegistratie: z.string(),
			bestandsnaam: z.string().max(255).nullable(),
			inhoud: z.string().url().nullable(),
			bestandsomvang: z.bigint().nullable(),
			link: z.string().max(200).nullable(),
			beschrijving: z.string().max(1000).nullable(),
			ontvangstdatum: z.string().nullable(),
			verzenddatum: z.string().nullable(),
			indicatieGebruiksrecht: z.boolean().nullable(),
			verschijningsvorm: z.string().nullable(),
			ondertekening: z.object({
				soort: z.enum(['analoog', 'digitaal', 'pki']),
				datum: z.string(),
			}).nullable(),
			integriteit: z.object({
				algoritme: z.enum(['crc_16', 'crc_32', 'crc_64', 'fletcher_4', 'fletcher_8', 'fletcher_16', 'fletcher_32', 'hmac', 'md5', 'sha_1', 'sha_256', 'sha_512', 'sha_3']),
				waarde: z.string().max(128),
				datum: z.string(),
			}).nullable(),
			informatieobjecttype: z.string().max(200),
			locked: z.boolean(),
			bestandsdelen: z.array(z.object({
				url: z.string().min(1).max(1000).url(),
				volgnummer: z.number(),
				omvang: z.number(),
				voltooid: z.boolean(),
				lock: z.string(),
			})),
			trefwoorden: z.array(z.string()).nullable(),
			_expand: z.object({
				informatieobjecttype: z.object({
					url: z.string().min(1).max(1000).url(),
					catalogus: z.string(),
					omschrijving: z.string().max(80),
					vertrouwelijkheidaanduiding: z.enum(['openbaar', 'beperkt_openbaar', 'intern', 'zaakvertrouwelijk', 'vertrouwelijk', 'confidentieel', 'geheim', 'zeer_geheim']),
					beginGeldigheid: z.string(),
					eindeGeldigheid: z.string().nullable(),
					beginObject: z.string().nullable(),
					eindeObject: z.string().nullable(),
					concept: z.boolean(),
					zaaktypen: z.string(),
					besluittypen: z.array(z.string()),
					informatieobjectcategorie: z.string().max(80),
					trefwoorden: z.array(z.string().max(30)).nullable(),
					omschrijvingGeneriek: z.object({
						informatieobjecttypeOmschrijvingGeneriek: z.string().max(80),
						definitieInformatieobjecttypeOmschrijvingGeneriek: z.string().max(255),
						herkomstInformatieobjecttypeOmschrijvingGeneriek: z.string().max(12),
						hierarchieInformatieobjecttypeOmschrijvingGeneriek: z.string().max(80),
						opmerkingInformatieobjecttypeOmschrijvingGeneriek: z.string().max(255).nullable(),
					}),
				}),
			}).nullable(),
		})

		return schema.safeParse(this)
	}

}
