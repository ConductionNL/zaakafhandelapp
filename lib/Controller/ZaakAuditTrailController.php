<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

/**
 * Stub controller for the ZGW audit-trail resource.
 *
 * Real audit-trail data has not yet been implemented. All data endpoints
 * return 501 Not Implemented so that audit systems and compliance checks
 * never receive fabricated test data in place of the real evidence trail.
 *
 * Routes for these endpoints have been removed from appinfo/routes.php until
 * a real implementation backed by ObjectService / CallService is in place.
 */
class ZaakAuditTrailController extends Controller
{
    public function __construct(
        $appName,
        IRequest $request
    ) {
        parent::__construct($appName, $request);
    }//end __construct()

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
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
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function index(): JSONResponse
    {
        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end index()

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function show(string $id): JSONResponse
    {
        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end show()

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function create(): JSONResponse
    {
        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end create()

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function update(string $id): JSONResponse
    {
        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end update()

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function destroy(string $id): JSONResponse
    {
        return new JSONResponse(
            ['error' => 'Audit trail is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end destroy()
}//end class
