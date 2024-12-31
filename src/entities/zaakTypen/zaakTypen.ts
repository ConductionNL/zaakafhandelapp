import { SafeParseReturnType, z } from 'zod'
import { TZaakType } from './zaakTypen.types'

export class ZaakType implements TZaakType {

	public id: string
	public identificatie: string
	public omschrijving: string
	public omschrijvingGeneriek: string
	public vertrouwelijkheidaanduiding: string
	public doel: string
	public aanleiding: string
	public toelichting: string
	public indicatieInternOfExtern: string
	public handelingInitiator: string
	public onderwerp: string
	public handelingBehandelaar: string
	public doorlooptijd: string
	public servicenorm: string
	public opschortingEnAanhoudingMogelijk: string
	public verlengingMogelijk: string
	public verlengingstermijn: string
	public publicatieIndicatie: string
	public publicatietekst: string
	public productenOfDiensten: string
	public selectielijstProcestype: string
	public referentieproces: string
	public catalogus: string
	public beginGeldigheid: string
	public eindeGeldigheid: string
	public beginObject: string
	public eindeObject: string
	public versiedatum: string

	constructor(source: TZaakType) {
		this.id = source.id || ''
		this.identificatie = source.identificatie || ''
		this.omschrijving = source.omschrijving || ''
		this.omschrijvingGeneriek = source.omschrijvingGeneriek || ''
		this.vertrouwelijkheidaanduiding = source.vertrouwelijkheidaanduiding || ''
		this.doel = source.doel || ''
		this.aanleiding = source.aanleiding || ''
		this.toelichting = source.toelichting || ''
		this.indicatieInternOfExtern = source.indicatieInternOfExtern || ''
		this.handelingInitiator = source.handelingInitiator || ''
		this.onderwerp = source.onderwerp || ''
		this.handelingBehandelaar = source.handelingBehandelaar || ''
		this.doorlooptijd = source.doorlooptijd || ''
		this.servicenorm = source.servicenorm || ''
		this.opschortingEnAanhoudingMogelijk = source.opschortingEnAanhoudingMogelijk || ''
		this.verlengingMogelijk = source.verlengingMogelijk || ''
		this.verlengingstermijn = source.verlengingstermijn || ''
		this.publicatieIndicatie = source.publicatieIndicatie || ''
		this.publicatietekst = source.publicatietekst || ''
		this.productenOfDiensten = source.productenOfDiensten || ''
		this.selectielijstProcestype = source.selectielijstProcestype || ''
		this.referentieproces = source.referentieproces || ''
		this.catalogus = source.catalogus || ''
		this.beginGeldigheid = source.beginGeldigheid || ''
		this.eindeGeldigheid = source.eindeGeldigheid || ''
		this.beginObject = source.beginObject || ''
		this.eindeObject = source.eindeObject || ''
		this.versiedatum = source.versiedatum || ''
	}

	public validate(): SafeParseReturnType<TZaakType, unknown> {
		const schema = z.object({
			id: z.string().optional(),
			identificatie: z.string().min(1),
			omschrijving: z.string().min(1),
			omschrijvingGeneriek: z.string().min(1),
			vertrouwelijkheidaanduiding: z.string().min(1),
			doel: z.string().min(1),
			aanleiding: z.string().min(1),
			toelichting: z.string().min(1),
			indicatieInternOfExtern: z.string().min(1),
			handelingInitiator: z.string().min(1),
			onderwerp: z.string().min(1),
			handelingBehandelaar: z.string().min(1),
			doorlooptijd: z.string().min(1),
			servicenorm: z.string().min(1),
			opschortingEnAanhoudingMogelijk: z.string().min(1),
			verlengingMogelijk: z.string().min(1),
			verlengingstermijn: z.string().min(1),
			publicatieIndicatie: z.string().min(1),
			publicatietekst: z.string().min(1),
			productenOfDiensten: z.string().min(1),
			selectielijstProcestype: z.string().min(1),
			referentieproces: z.string().min(1),
			catalogus: z.string().min(1),
			beginGeldigheid: z.string().min(1),
			eindeGeldigheid: z.string().min(1),
			beginObject: z.string().min(1),
			eindeObject: z.string().min(1),
			versiedatum: z.string().min(1),
		})

		return schema.safeParse(this)
	}

}
