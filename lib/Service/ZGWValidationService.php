<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;
use OCP\AppFramework\Db\DoesNotExistException;

/**
 * Validation service for ZGW cross-object references.
 *
 * Handles relevanteAndereZaken and besluitInformatieObject validation.
 * Zaak-specific field validation is in ZGWZaakValidationService.
 */
class ZGWValidationService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    /**
     * @param ObjectMapperService $mapperService The mapper service
     */
    public function __construct(ObjectMapperService $mapperService)
    {
        $this->objectService = $mapperService->getOpenRegisters();
    }//end __construct()

    /**
     * ZRC-010: Validate relevanteAndereZaken references.
     */
    public function checkRelevanteAndereZaken(ObjectEntity $zaak): void
    {
        $zaakArray = $zaak->jsonSerialize();

        if (is_array($zaakArray['relevanteAndereZaken']) === false) {
            return;
        }

        $i = 0;
        foreach ($zaakArray['relevanteAndereZaken'] as $relevanteZaak) {
            $this->objectService->clearCurrents();
            if (isset($relevanteZaak['url']) === false) {
                $i++;
                continue;
            }

            try {
                $id = explode('/', $relevanteZaak['url']);
                $this->objectService->clearCurrents();
                $this->objectService->find(end($id));
                $this->objectService->clearCurrents();
            } catch (DoesNotExistException $exception) {
                throw new CustomValidationException(
                    "Relevante zaak bestaat niet",
                    [['name' => "relevanteAndereZaken.$i.url", 'code' => 'bad-url', 'reason' => 'De relevante zaak bestaat niet of is niet benaderbaar']]
                );
            }

            $i++;
        }//end foreach
    }//end checkRelevanteAndereZaken()

    /**
     * Validate a BesluitInformatieObject's type against besluittype.
     */
    public function validateBesluitInformatieObject(ObjectEntity $bio): void
    {
        $arr = $bio->jsonSerialize();

        $eio     = $this->findByUrl($arr['informatieobject'], ['informatieobjecttype']);
        $besluit = $this->findByUrl($arr['besluit'], ['besluittype']);

        $iot = $eio->jsonSerialize()['informatieobjecttype']['omschrijving'];

        if (in_array(needle: $iot, haystack: $besluit->jsonSerialize()['besluittype']['informatieobjecttypen']) === false) {
            throw new CustomValidationException(
                'Informatieobjecttype niet in besluittype',
                [['name' => 'nonFieldErrors', 'code' => 'invalid-informatieobjecttype', 'reason' => 'informatieobjecttype niet aanwezig op besluittype']]
            );
        }
    }//end validateBesluitInformatieObject()

    private function findByUrl(string $url, array $extend=[]): ObjectEntity
    {
        $parts = explode('/', $url);
        $this->objectService->clearCurrents();
        return $this->objectService->find(id: end($parts), extend: $extend);
    }//end findByUrl()
}//end class
