export type TRol = {
    id: string;
    url: string;
    uuid: string;
    zaak: string;
    betrokkene?: string;
    betrokkeneType: 'natuurlijk_persoon' | 'niet_natuurlijk_persoon' | 'vestiging' | 'organisatorische_eenheid' | 'medewerker';
    afwijkendeNaamBetrokkene?: string;
    roltype: string;
    omschrijving: string;
    omschrijvingGeneriek: 'adviseur' | 'behandelaar' | 'belanghebbende' | 'beslisser' | 'initiator' | 'klantcontacter' | 'zaakcoordinator' | 'mede_initiator';
    roltoelichting: string;
    registratiedatum: string;
    indicatieMachtiging?: 'gemachtigde' | 'machtiginggever' | '';
    contactpersoonRol?: {
        emailadres?: string;
        functie?: string;
        telefoonnummer?: string;
        naam: string;
    };
    statussen: string[];
    _expand: {
        zaak?: string;
        roltype?: string;
        statussen?: string;
    };
    betrokkeneIdentificatie?: {
        identificatie?: string;
        achternaam?: string;
        voorletters?: string;
        voorvoegselAchternaam?: string;
    };
}
