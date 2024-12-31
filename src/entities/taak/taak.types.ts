export type ZaakID = string; // create an alias for string called ZaakID to make it easier for developers to understand that this is a ID from a Zaak

export type TTaak = {
    id: string;
    title: string;
    zaak: ZaakID;
    type: string;
    status: string;
    deadline: string;
    onderwerp: string;
    toelichting: string;
    actie: string;
	klant: string;
	medewerker: string;
	contactmoment: string;
}
