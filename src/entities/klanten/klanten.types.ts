export type TKlantType = 'persoon' | 'organisatie'

export type TKlant = {
    id: string;
    type: TKlantType;
    voornaam: string;
    tussenvoegsel: string;
    achternaam: string;
    bsn: string;
    geboortedatum: string;
    isMale: boolean;
    land: string;
    telefoonnummer: string;
    emailadres: string;
    straatnaam: string;
    plaats: string;
    postcode: string;
    huisnummer: string;
    functie: string;
    aanmaakkanaal: string;
    bronorganisatie: string;
    bedrijfsnaam: string;
    kvkNummer: string;
    websiteUrl: string;
    url: string;
    geverifieerd: string;
    subject: string;
    subjectIdentificatie: string;
    subjectType: string;
}
