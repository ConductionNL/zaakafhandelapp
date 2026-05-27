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
     * ZGW confidentiality ordering, lowest to highest.
     * A zaak may only have a classification equal to or more restrictive than its zaaktype.
     */
    private const VERTROUWELIJKHEID_ORDER = [
        'openbaar'             => 0,
        'beperkt_openbaar'     => 1,
        'intern'               => 2,
        'zaakvertrouwelijk'    => 3,
        'vertrouwelijk'        => 4,
        'confidentieel'        => 5,
        'geheim'               => 6,
        'zeer_geheim'          => 7,
    ];

    /**
     * ZRC-009: Set derived vertrouwelijkheidaanduiding.
     *
     * When the zaak has no classification yet, inherit the zaaktype default.
     * When the zaak already has a classification, enforce that it is at least as
     * restrictive as the zaaktype minimum — a lower classification is replaced with
     * the zaaktype value (fixes #281, ZGW confidentiality lowering rule).
     *
     * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-003
     */
    public function setVertrouwelijkheidaanduiding(ObjectEntity $zaak): void
    {
        $zaakArray   = $zaak->jsonSerialize();
        $zaaktype    = $this->find($zaakArray['zaaktype']);
        $ztMinimum   = $zaaktype->jsonSerialize()['vertrouwelijkheidaanduiding'] ?? null;

        if ($ztMinimum === null) {
            // Zaaktype has no classification configured; nothing to enforce.
            return;
        }

        $current = $zaakArray['vertrouwelijkheidaanduiding'] ?? null;

        if ($current === null) {
            // Inherit the zaaktype default when no classification has been set yet.
            $zaakArray['vertrouwelijkheidaanduiding'] = $ztMinimum;
        } else {
            // Enforce minimum: if the zaak's classification is lower than the zaaktype
            // minimum, raise it to the minimum.
            $currentRank = self::VERTROUWELIJKHEID_ORDER[$current] ?? -1;
            $minimumRank = self::VERTROUWELIJKHEID_ORDER[$ztMinimum] ?? 0;

            if ($currentRank < $minimumRank) {
                $zaakArray['vertrouwelijkheidaanduiding'] = $ztMinimum;
            }
        }

        if ($zaakArray['vertrouwelijkheidaanduiding'] === ($zaak->jsonSerialize()['vertrouwelijkheidaanduiding'] ?? null)) {
            // No change needed; skip the save to avoid an unnecessary write.
            return;
        }

        $zaak->setObject($zaakArray);
        $this->objectService->saveObject(object: $zaak, register: $zaak->getRegister(), schema: $zaak->getSchema());
    }//end setVertrouwelijkheidaanduiding()

    private function find(string $url, array $extend=[]): ObjectEntity
    {
        $this->objectService->clearCurrents();
        return $this->objectService->find(id: $this->registry->getObjectIdByEndpointUrl($url), extend: $extend);
    }//end find()
}//end class
