export type TKlantType = 'persoon' | 'organisatie'

export type TKlant = {
    id: string;
    type: TKlantType;
    voornaam: string;
    tussenvoegsel: string;
    achternaam: string;
    telefoonnummer: string;
    emailadres: string;
    functie: string;
    aanmaakkanaal: string;
    bronorganisatie: string;
    bedrijfsnaam: string;
    websiteUrl: string;
    url: string;
    geverifieerd: string;
    subject: string;
    subjectIdentificatie: string;
    subjectType: string;
}
