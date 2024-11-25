import { SafeParseReturnType, z } from 'zod'
import { TZaak, zaakTypeID } from './zaak.types'

export class Zaak implements TZaak {

	public id: string
	public uuid: string
	public omschrijving: string
	public identificatie: string
	public url: string
	public bronorganisatie: string
	public toelichting: string
	public zaaktype: zaakTypeID
	public archiefstatus: string
	public registratiedatum: string
	public verantwoordelijkeOrganisatie: string
	public startdatum: string
	public einddatum: string
	public einddatumGepland: string
	public uiterlijkeEinddatumAfdoening: string
	public publicatiedatum: string
	public communicatiekanaal: string
	public betalingsindicatie: string
	public betalingsindicatieWeergave: string
	public laatsteBetaaldatum: string
	public selectielijstklasse: string
	public hoofdzaak: string
	public klant: string

	constructor(source: TZaak) {
		this.id = source.id || ''
		this.uuid = source.uuid || ''
		this.omschrijving = source.omschrijving || ''
		this.identificatie = source.identificatie || ''
		this.url = source.url || ''
		this.bronorganisatie = source.bronorganisatie || ''
		this.toelichting = source.toelichting || ''
		this.zaaktype = source.zaaktype || ''
		this.archiefstatus = source.archiefstatus || ''
		this.registratiedatum = source.registratiedatum || ''
		this.verantwoordelijkeOrganisatie = source.verantwoordelijkeOrganisatie || ''
		this.startdatum = source.startdatum || ''
		this.einddatum = source.einddatum || ''
		this.einddatumGepland = source.einddatumGepland || ''
		this.uiterlijkeEinddatumAfdoening = source.uiterlijkeEinddatumAfdoening || ''
		this.publicatiedatum = source.publicatiedatum || ''
		this.communicatiekanaal = source.communicatiekanaal || ''
		this.betalingsindicatie = source.betalingsindicatie || ''
		this.betalingsindicatieWeergave = source.betalingsindicatieWeergave || ''
		this.laatsteBetaaldatum = source.laatsteBetaaldatum || ''
		this.selectielijstklasse = source.selectielijstklasse || ''
		this.hoofdzaak = source.hoofdzaak || ''
		this.klant = source.klant || ''
	}

	public validate(): SafeParseReturnType<TZaak, unknown> {
		const schema = z.object({
			id: z.string().optional(),
			uuid: z.string().optional(),
			omschrijving: z.string().min(1),
			identificatie: z.string().min(1),
			url: z.string().url(),
			bronorganisatie: z.string(),
			toelichting: z.string(),
			zaaktype: z.string(),
			archiefstatus: z.string(),
			registratiedatum: z.string(),
			verantwoordelijkeOrganisatie: z.string(),
			startdatum: z.string(),
			einddatum: z.string(),
			einddatumGepland: z.string(),
			uiterlijkeEinddatumAfdoening: z.string(),
			publicatiedatum: z.string(),
			communicatiekanaal: z.string(),
			betalingsindicatie: z.string(),
			betalingsindicatieWeergave: z.string(),
			laatsteBetaaldatum: z.string(),
			selectielijstklasse: z.string(),
			hoofdzaak: z.string(),
			klant: z.string(),
		})

		return schema.safeParse(this)
	}

}
