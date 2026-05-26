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
        $sa = $status->jsonSerialize();
        if ($this->closeService->isEindStatus($sa)) {
            return;
        }

        $zaak = $this->find($sa['zaak']);
        $za   = $zaak->jsonSerialize();
        $za['einddatum'] = $za['archiefactiedatum'] = $za['archiefnominatie'] = null;
        $zaak->setObject($za);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }//end reopenZaak()

    /**
     * Delete dependent objects. ZRC-023.
      *
      * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-003
     */
    public function deleteZaak(ObjectEntity $zaak): void
    {
        $a    = $this->objectService->renderEntity($zaak);
        $urls = array_merge($a['rollen'] ?? [], $a['eigenschappen'] ?? [], [$a['resultaat']], $a['statussen'] ?? [], $a['deelzaken'] ?? [], $a['zaakobjecten'] ?? [], [$a['klantcontact']]);

        $ids = array_filter(array_map(fn(?string $u) => $u ? $this->registry->getObjectIdByEndpointUrl($u) : null, $urls));
        $this->objectService->deleteObjects($ids);

        foreach ($a['zaakinformatieobjecten'] as $u) {
            $this->objectService->deleteObject($this->registry->getObjectIdByEndpointUrl($u));
        }
    }//end deleteZaak()

    /**
     * ZRC-009: Set derived vertrouwelijkheidaanduiding.
      *
      * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-003
     */
    public function setVertrouwelijkheidaanduiding(ObjectEntity $zaak): void
    {
        $za = $zaak->jsonSerialize();
        if ($za['vertrouwelijkheidaanduiding'] !== null) {
            return;
        }

        $zt = $this->find($za['zaaktype']);
        $za['vertrouwelijkheidaanduiding'] = $zt->jsonSerialize()['vertrouwelijkheidaanduiding'];
        $zaak->setObject($za);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }//end setVertrouwelijkheidaanduiding()

    private function find(string $url, array $extend=[]): ObjectEntity
    {
        $this->objectService->clearCurrents();
        return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), extend: $extend);
    }//end find()
}//end class
