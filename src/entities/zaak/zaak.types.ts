export type zaakTypeID = string // create an alias for string called ZaakTypeID to make it easier for developers to understand that this is a ID from a ZaakType

export type TZaak = {
    id: string;
    uuid: string;
    omschrijving: string;
    identificatie: string;
    url: string;
    bronorganisatie: string;
    toelichting: string;
    zaaktype: zaakTypeID;
    archiefstatus: string;
    registratiedatum: string;
    verantwoordelijkeOrganisatie: string;
    startdatum: string;
    einddatum: string;
    einddatumGepland: string;
    uiterlijkeEinddatumAfdoening: string;
    publicatiedatum: string;
    communicatiekanaal: string;
    betalingsindicatie: string;
    betalingsindicatieWeergave: string;
    laatsteBetaaldatum: string;
    selectielijstklasse: string;
    hoofdzaak: string;
}
