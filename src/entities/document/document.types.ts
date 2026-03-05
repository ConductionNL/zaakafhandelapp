// https://vng-realisatie.github.io/gemma-zaken/standaard/documenten/redoc-1.5.0#tag/enkelvoudiginformatieobjecten/operation/enkelvoudiginformatieobject_retrieve

export type TDocument = {
    id: string;
    zaak?: string; // zaak id property to link to a zaak, this is not in the gemma-zaken documentation
    url: string; // min 1 character, max 1000 characters, string as uri
    identificatie?: string; // max 40 characters
    bronorganisatie: string; // max 9 characters
    creatiedatum: string; // string as iso date time string
    titel: string; // max 200 characters
    vertrouwelijkheidaanduiding?: 'openbaar' | 'beperkt_openbaar' | 'intern' | 'zaakvertrouwelijk' | 'vertrouwelijk' | 'confidentieel' | 'geheim' | 'zeer_geheim';
    auteur: string; // max 200 characters
    status?: 'in_bewerking' | 'ter_vaststelling' | 'definitief' | 'gearchiveerd';
    inhoudIsVervallen?: boolean;
    formaat?: string; // max 255 characters
    taal: string; // 3 characters, ISO 639-2/B language code
    versie: number;
    beginRegistratie: string; // string as iso date time string
    bestandsnaam?: string; // max 255 characters
    inhoud?: string; // string as uri
    bestandsomvang?: bigint; // int64
    link?: string; // max 200 characters
    beschrijving?: string; // max 1000 characters
    ontvangstdatum?: string; // deprecated
    verzenddatum?: string; // deprecated
    indicatieGebruiksrecht?: boolean;
    verschijningsvorm?: string;
    ondertekening?: {
        soort: 'analoog' | 'digitaal' | 'pki';
        datum: string; // string as iso date time string
    };
    integriteit?: {
        algoritme: 'crc_16' | 'crc_32' | 'crc_64' | 'fletcher_4' | 'fletcher_8' | 'fletcher_16' | 'fletcher_32' | 'hmac' | 'md5' | 'sha_1' | 'sha_256' | 'sha_512' | 'sha_3';
        waarde: string; // max 128 characters
        datum: string;
    };
    informatieobjecttype: string; // max 200 characters
    locked: boolean;
    bestandsdelen: {
        url: string; // min 1 character, max 1000 characters, string as uri
        volgnummer: number;
        omvang: number;
        voltooid: boolean;
        lock: string;
    }[];
    trefwoorden?: string[];
    _expand?: {
        informatieobjecttype: {
            url: string; // min 1 character, max 1000 characters, string as uri
            catalogus: string;
            omschrijving: string; // max 80 characters
            vertrouwelijkheidaanduiding: 'openbaar' | 'beperkt_openbaar' | 'intern' | 'zaakvertrouwelijk' | 'vertrouwelijk' | 'confidentieel' | 'geheim' | 'zeer_geheim';
            beginGeldigheid: string; // string as iso date time string
            eindeGeldigheid?: string; // string as iso date time string
            beginObject?: string; // string as iso date time string
            eindeObject?: string; // string as iso date time string
            concept: boolean;
            zaaktypen: string;
            besluittypen: string[]; // string as uri, has to be unique
            informatieobjectcategorie: string; // max 80 characters
            trefwoorden?: string[]; // max 30 characters per string
            omschrijvingGeneriek: {
                informatieobjecttypeOmschrijvingGeneriek: string; // max 80 characters
                definitieInformatieobjecttypeOmschrijvingGeneriek: string; // max 255 characters
                herkomstInformatieobjecttypeOmschrijvingGeneriek: string; // max 12 characters
                hierarchieInformatieobjecttypeOmschrijvingGeneriek: string; // max 80 characters
                opmerkingInformatieobjecttypeOmschrijvingGeneriek?: string; // max 255 characters
            }
        }
    }
}
