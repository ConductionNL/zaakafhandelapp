<?php

namespace OCA\ZaakAfhandelApp\Controller;

use Exception;
use OCA\ZaakAfhandelApp\Service\ObjectService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;

/**
 * ZGW ZRC zaak audit-trail controller.
 *
 * Serves the ZGW audit trail of a zaak on /api/zrc/zaken/{zaak_uuid}/audit_trail,
 * derived from the OpenRegister object audit trail of that zaak and mapped onto
 * the ZGW Audittrail shape. The audit trail is read-only per the ZRC standard:
 * write verbs return 405 Method Not Allowed with an Allow: GET header. Replaces
 * the former not-implemented stub.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZaakAuditTrailController extends Controller
{
    /**
     * Maps OpenRegister audit actions onto the ZGW `actie` vocabulary.
     *
     * @var array<string, string>
     */
    private const ACTIE_MAP = [
        'create'  => 'create',
        'created' => 'create',
        'update'  => 'update',
        'updated' => 'update',
        'delete'  => 'destroy',
        'deleted' => 'destroy',
        'destroy' => 'destroy',
    ];

    public function __construct(
        $appName,
        IRequest $request,
        private readonly ObjectService $objectService,
        private readonly IURLGenerator $urlGenerator,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * This returns the template of the main app's page.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-005
     */
    public function page(): TemplateResponse
    {
        return new TemplateResponse(
            'zaakafhandelapp',
            'index',
            []
        );
    }//end page()

    /**
     * List the ZGW audit trail of the routed zaak.
     *
     * @param string $zaakUuid The zaak whose audit trail is requested (route).
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-007
     */
    public function index(string $zaakUuid): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $entries = $this->objectService->getAuditTrail('zaken', $zaakUuid);
            $mapped  = array_map(fn (array $entry): array => $this->mapAuditTrail($entry, $zaakUuid), $entries);

            return new JSONResponse(['results' => array_values($mapped)]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }//end index()

    /**
     * Read a single ZGW audit-trail entry of the routed zaak.
     *
     * @param string $zaakUuid The zaak whose audit trail is requested (route).
     * @param string $id       The audit-trail entry uuid.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-007
     */
    public function show(string $zaakUuid, string $id): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $entries = $this->objectService->getAuditTrail('zaken', $zaakUuid);

            foreach ($entries as $entry) {
                $mapped = $this->mapAuditTrail((array) $entry, $zaakUuid);
                if (($mapped['uuid'] ?? null) === $id) {
                    return new JSONResponse($mapped);
                }
            }

            return new JSONResponse(['error' => 'Audit trail entry not found.'], Http::STATUS_NOT_FOUND);
        } catch (Exception $e) {
            return new JSONResponse(['error' => 'Audit trail entry not found.'], Http::STATUS_NOT_FOUND);
        }
    }//end show()

    /**
     * The ZGW audit trail is read-only — creating is not allowed.
     *
     * @param string $zaakUuid The routed zaak.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-007
     *
     * @no-admin-idor-exempt Read-only audit trail: this verb takes no caller-supplied object action and always returns 405 Method Not Allowed (Allow: GET) without touching any object.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $zaakUuid is part of the NC route signature.
     */
    public function create(string $zaakUuid): JSONResponse
    {
        return $this->methodNotAllowed();
    }//end create()

    /**
     * The ZGW audit trail is read-only — updating is not allowed.
     *
     * @param string $zaakUuid The routed zaak.
     * @param string $id       The audit-trail entry uuid.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-007
     *
     * @no-admin-idor-exempt Read-only audit trail: this verb takes no caller-supplied object action and always returns 405 Method Not Allowed (Allow: GET) without touching any object.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $zaakUuid/$id are part of the NC route signature.
     */
    public function update(string $zaakUuid, string $id): JSONResponse
    {
        return $this->methodNotAllowed();
    }//end update()

    /**
     * The ZGW audit trail is read-only — deleting is not allowed.
     *
     * @param string $zaakUuid The routed zaak.
     * @param string $id       The audit-trail entry uuid.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-zaak-management/spec.md#REQ-007
     *
     * @no-admin-idor-exempt Read-only audit trail: this verb takes no caller-supplied object action and always returns 405 Method Not Allowed (Allow: GET) without touching any object.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) — $zaakUuid/$id are part of the NC route signature.
     */
    public function destroy(string $zaakUuid, string $id): JSONResponse
    {
        return $this->methodNotAllowed();
    }//end destroy()

    /**
     * Builds the 405 Method Not Allowed response with an Allow: GET header.
     *
     * @return JSONResponse
     */
    private function methodNotAllowed(): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $response = new JSONResponse(
            ['error' => 'The audit trail is read-only.'],
            Http::STATUS_METHOD_NOT_ALLOWED
        );
        $response->addHeader('Allow', 'GET');

        return $response;
    }//end methodNotAllowed()

    /**
     * Maps an OpenRegister audit entry onto the ZGW Audittrail shape.
     *
     * Defensive against missing keys: untracked values degrade to null.
     *
     * @param array  $entry    The OpenRegister audit entry.
     * @param string $zaakUuid The zaak the trail belongs to.
     *
     * @return array The ZGW Audittrail resource.
     */
    private function mapAuditTrail(array $entry, string $zaakUuid): array
    {
        $action = strtolower((string) ($entry['action'] ?? $entry['actie'] ?? ''));
        $actie  = (self::ACTIE_MAP[$action] ?? ($action !== '' ? $action : null));

        $changes = ($entry['changed'] ?? $entry['changes'] ?? $entry['wijzigingen'] ?? null);

        $zaakUrl = $this->urlGenerator->getAbsoluteURL(
            '/index.php/apps/zaakafhandelapp/api/zrc/zaken/'.$zaakUuid
        );

        return [
            'uuid'               => ($entry['uuid'] ?? $entry['id'] ?? null),
            'bron'               => 'ZRC',
            'applicatieWeergave' => 'Zaak Afhandel App',
            'gebruikersId'       => ($entry['user'] ?? $entry['userId'] ?? $entry['gebruikersId'] ?? null),
            'gebruikersWeergave' => ($entry['userName'] ?? $entry['gebruikersWeergave'] ?? null),
            'actie'              => $actie,
            'actieWeergave'      => ($entry['actionLabel'] ?? $entry['actieWeergave'] ?? $actie),
            'resultaat'          => ($entry['result'] ?? $entry['resultaat'] ?? null),
            'hoofdObject'        => $zaakUrl,
            'resource'           => 'zaak',
            'resourceUrl'        => $zaakUrl,
            'resourceWeergave'   => ($entry['resourceLabel'] ?? $entry['resourceWeergave'] ?? null),
            'aanmaakdatum'       => ($entry['created'] ?? $entry['aanmaakdatum'] ?? ($entry['timestamp'] ?? null)),
            'wijzigingen'        => $this->mapChanges($changes),
        ];
    }//end mapAuditTrail()

    /**
     * Normalises an audit change record into the ZGW wijzigingen shape.
     *
     * @param mixed $changes The raw change record.
     *
     * @return array{oud: mixed, nieuw: mixed}
     */
    private function mapChanges(mixed $changes): array
    {
        if (is_array($changes) === false) {
            return ['oud' => null, 'nieuw' => null];
        }

        return [
            'oud'   => ($changes['old'] ?? $changes['oud'] ?? ($changes['before'] ?? null)),
            'nieuw' => ($changes['new'] ?? $changes['nieuw'] ?? ($changes['after'] ?? null)),
        ];
    }//end mapChanges()
}//end class
