export type BerichtID = string; // create an alias for string called BerichtID to make it easier for developers to understand that this is an ID from a Bericht

export type TBericht = {
    id: string;
    batchID: string;
    aanmaakDatum: string;
    berichtLeverancierID: string;
    berichtID: BerichtID;
    berichtType: string;
    publicatieDatum: string;
    onderwerp: string;
    berichttekst: string;
    referentie: string;
    gebruikerID: string;
    soortGebruiker: string;
    inhoud: string;
    bijlageType: string;
    omschrijving: string;
    volgorde: string;
}
