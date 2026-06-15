<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCA\ZaakAfhandelApp\Service\CallService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Controller for rollen (roles) resources.
 *
 * @see https://vng-realisatie.github.io/gemma-zaken/standaard/zaken/
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class RollenController extends Controller
{
    /**
     * RollenController constructor.
     *
     * @param string       $appName     The application name
     * @param IRequest     $request     The request object
     * @param IAppConfig   $config      The app configuration
     * @param IUserSession $userSession The user session
     */
    public function __construct(
        $appName,
        IRequest $request,
        private readonly IAppConfig $config,
        private readonly IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * Renders the main application page.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-003
     */
    public function page(): TemplateResponse
    {
        return new TemplateResponse('zaakafhandelapp', 'index', []);
    }//end page()

    /**
     * Return (and search) all rollen.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param CallService $callService The call service
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function index(CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $results = $callService->index(source: 'zrc', endpoint: 'rollen');
        return new JSONResponse($results);
    }//end index()

    /**
     * Read a single rol.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string      $id          The rol ID
     * @param CallService $callService The call service
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-001
     */
    public function show(string $id, CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $results = $callService->show(source: 'zrc', endpoint: 'rollen', id: $id);
        return new JSONResponse($results);
    }//end show()

    /**
     * Valid ZGW betrokkeneType values per VNG API standard.
     */
    private const BETROKKENE_TYPES = [
        'natuurlijk_persoon',
        'niet_natuurlijk_persoon',
        'vestiging',
        'organisatorische_eenheid',
        'medewerker',
    ];

    /**
     * Create a rol.
     *
     * Validates ZGW mandatory invariants before forwarding to the ZRC:
     * - betrokkeneType must be a known enum value.
     * - roltoelichting is mandatory as the AVG legal basis for processing personal data
     *   (BSN stored in betrokkeneIdentificatie.inpBsn) — fixes #279.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param CallService $callService The call service
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function create(CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $body = $this->request->getParams();

        // Validate betrokkeneType enum.
        if (isset($body['betrokkeneType']) && in_array($body['betrokkeneType'], self::BETROKKENE_TYPES, true) === false) {
            return new JSONResponse(
                ['betrokkeneType' => ['Waarde \''.$body['betrokkeneType'].'\' is geen geldige betrokkeneType.']],
                Http::STATUS_BAD_REQUEST
            );
        }

        // Enforce roltoelichting: required for AVG Article 5(1)(b) purpose-limitation
        // when personal data (e.g. BSN) is associated with the rol.
        if (empty($body['roltoelichting']) === true) {
            return new JSONResponse(
                ['roltoelichting' => ['Dit veld is verplicht. Geef de juridische grondslag op voor het verwerken van persoonsgegevens in deze rol.']],
                Http::STATUS_BAD_REQUEST
            );
        }

        $results = $callService->create(source: 'zrc', endpoint: 'rollen', data: $body);
        return new JSONResponse($results);
    }//end create()

    /**
     * Update a rol.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string      $id          The rol ID
     * @param CallService $callService The call service
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function update(string $id, CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $body    = $this->request->getParams();
        $results = $callService->update(source: 'zrc', endpoint: 'rollen', data: $body, id: $id);
        return new JSONResponse($results);
    }//end update()

    /**
     * Delete a rol.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string      $id          The rol ID
     * @param CallService $callService The call service
     *
     * @return JSONResponse
     *
     * @spec openspec/specs/zgw-related-resources/spec.md#REQ-002
     */
    public function destroy(string $id, CallService $callService): JSONResponse
    {
        if ($this->userSession->getUser() === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        $callService->destroy(source: 'zrc', endpoint: 'rollen', id: $id);
        return new JSONResponse([]);
    }//end destroy()
}//end class
