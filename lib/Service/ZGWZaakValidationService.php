<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Exception\CustomValidationException;

/**
 * Validation service for zaak-specific field validation.
 */
class ZGWZaakValidationService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    public function __construct(ObjectMapperService $mapperService)
    {
        $this->objectService = $mapperService->getOpenRegisters();
    }//end __construct()

    /**
     * ZRC-015: Check productenOfDiensten against zaaktype.
     */
    public function checkProductenOfDiensten(ObjectEntity $zaak): void
    {
        $arr = $zaak->jsonSerialize();

        if (is_array($arr['productenOfDiensten']) === false) {
            return;
        }

        $ztId = explode('/', $arr['zaaktype']);
        $this->objectService->clearCurrents();
        $zt = $this->objectService->find(end($ztId));

        if (array_diff($arr['productenOfDiensten'], $zt->jsonSerialize()['productenOfDiensten']) !== []) {
            $this->throwValidationError('productenOfDiensten', 'invalid-products-services', 'Producten niet aanwezig op zaaktype');
        }
    }//end checkProductenOfDiensten()

    private function throwValidationError(string $name, string $code, string $reason): void
    {
        throw new CustomValidationException($reason, [['name' => $name, 'code' => $code, 'reason' => $reason]]);
    }//end throwValidationError()

    /**
     * ZRC-022: Check archive prerequisites.
     */
    public function checkArchivePrerequisites(ObjectEntity $zaak): void
    {
        $arr = $this->objectService->renderEntity($zaak);

        if ($arr['archiefstatus'] === 'nog_te_archiveren') {
            return;
        }

        $this->validateEioStatuses($arr);

        if ($arr['archiefnominatie'] === null) {
            $this->throwValidationError('archiefnominatie', 'archiefnominatie-not-set', 'De archiefnominatie moet geset zijn');
        }

        if ($arr['archiefactiedatum'] === null) {
            $this->throwValidationError('archiefactiedatum', 'archiefactiedatum-not-set', 'De archiefactiedatum moet geset zijn');
        }
    }//end checkArchivePrerequisites()

    /**
     * ZRC-012: Check verlenging and opschorting parameters.
     */
    public function checkGegevensgroepen(ObjectEntity $zaak): void
    {
        $arr = $zaak->jsonSerialize();

        if ($arr['verlenging'] !== null) {
            $this->validateRequiredFields($arr['verlenging'], 'verlenging', ['reden', 'duur'], "Verlenging is incorrect");
        }

        if ($arr['opschorting'] !== null) {
            $this->validateRequiredFields($arr['opschorting'], 'opschorting', ['indicatie', 'reden'], "Opschorting is incorrect");
        }
    }//end checkGegevensgroepen()

    private function validateEioStatuses(array $arr): void
    {
        $zioIds = array_map(
                function ($zio) {
                    $e = explode('/', $zio);
                    return end($e);
                },
                $arr['zaakinformatieobjecten']
                );

        $this->objectService->clearCurrents();
        $zios     = $this->objectService->findAll(['ids' => $zioIds, 'extend' => ['informatieobject']]);
        $statuses = array_unique(array_map(fn(ObjectEntity $z) => $z->jsonSerialize()['informatieobject']['status'] ?? null, $zios));

        if (count($statuses) !== 1 || $statuses[0] !== 'gearchiveerd') {
            $this->throwValidationError(
                'zaakinformatieobjecten',
                'informatieobject-status-not-set',
                'Alle informatieobjecten moeten status gearchiveerd hebben.'
            );
        }
    }//end validateEioStatuses()

    private function validateRequiredFields(array $data, string $group, array $fields, string $message): void
    {
        $errors = [];
        foreach ($fields as $field) {
            if (isset($data[$field]) === false) {
                $errors[] = ['name' => "$group.$field", 'code' => 'required', 'reason' => "Het veld $field is verplicht"];
            }
        }

        if (count($errors) > 0) {
            throw new CustomValidationException($message, $errors);
        }
    }//end validateRequiredFields()
}//end class
