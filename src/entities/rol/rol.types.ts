export type TRol = {
    id?: string;
    uuid: string;
    omschrijving: string;
    omschrijvingGeneriek: string;
    url: string;
    zaak: string; // zaak id
    betrokkene: string;
    betrokkeneType: string;
    afwijkendeNaamBetrokkene: string;
    roltype: string;
    roltoelichting: string;
    registratiedatum: string;
    indicatieMachtiging: string;
    contactpersoonRol: {
        emailadres?: string
        functie?: string
        telefoonnummer?: string
        naam?: string
    };
    statussen: string[];
    _expand: {
        zaak?: string
        roltype?: string
        statussen?: string
    };
    betrokkeneIdentificatie: {
        identificatie?: string
        achternaam?: string
        voorletters?: string
        voorvoegselAchternaam?: string
    };
}
