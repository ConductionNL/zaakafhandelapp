<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;

/**
 * Handles zaak lifecycle: reopen, delete, vertrouwelijkheidaanduiding.
 * Close is handled by ZGWZaakCloseService.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class ZGWZaakLifecycleService
{

    private \OCA\OpenRegister\Service\ObjectService $objectService;

    public function __construct(
        ObjectMapperService $mapperService,
        private ZGWZaakCloseService $closeService,
        private ZGWRegistryService $registry,
    ) {
        $this->objectService = $mapperService->getOpenRegisters();
    }//end __construct()

    /**
     * Close a zaak. Delegates to ZGWZaakCloseService.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-002
     */
    public function closeZaak(ObjectEntity $status): void
    {
        $this->closeService->closeZaak($status);
    }//end closeZaak()

    /**
     * Reopen a zaak when non-eindstatus is set. ZRC-008.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-002
     */
    public function reopenZaak(ObjectEntity $status): void
    {
        $statusArray = $status->jsonSerialize();
        if ($this->closeService->isEindStatus($statusArray)) {
            return;
        }

        $zaak      = $this->find($statusArray['zaak']);
        $zaakArray = $zaak->jsonSerialize();
        $zaakArray['einddatum'] = $zaakArray['archiefactiedatum'] = $zaakArray['archiefnominatie'] = null;
        $zaak->setObject($zaakArray);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }//end reopenZaak()

    /**
     * Delete dependent objects. ZRC-023.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-003
     */
    public function deleteZaak(ObjectEntity $zaak): void
    {
        $arr  = $this->objectService->renderEntity($zaak);
        $urls = array_merge($arr['rollen'] ?? [], $arr['eigenschappen'] ?? [], [$arr['resultaat']], $arr['statussen'] ?? [], $arr['deelzaken'] ?? [], $arr['zaakobjecten'] ?? [], [$arr['klantcontact']]);

        $ids = array_filter(array_map(fn(?string $url) => $url ? $this->registry->getObjectIdByEndpointUrl($url) : null, $urls));
        $this->objectService->deleteObjects($ids);

        foreach ($arr['zaakinformatieobjecten'] as $zioUrl) {
            $this->objectService->deleteObject($this->registry->getObjectIdByEndpointUrl($zioUrl));
        }
    }//end deleteZaak()

    /**
     * ZRC-009: Set derived vertrouwelijkheidaanduiding.
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-003
     */
    public function setVertrouwelijkheidaanduiding(ObjectEntity $zaak): void
    {
        $zaakArray = $zaak->jsonSerialize();
        if ($zaakArray['vertrouwelijkheidaanduiding'] !== null) {
            return;
        }

        $zaaktype = $this->find($zaakArray['zaaktype']);
        $zaakArray['vertrouwelijkheidaanduiding'] = $zaaktype->jsonSerialize()['vertrouwelijkheidaanduiding'];
        $zaak->setObject($zaakArray);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }//end setVertrouwelijkheidaanduiding()

    private function find(string $url, array $extend=[]): ObjectEntity
    {
        $this->objectService->clearCurrents();
        return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), extend: $extend);
    }//end find()
}//end class
