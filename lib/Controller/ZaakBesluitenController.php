<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

/**
 * Stub controller for the ZGW zaakbesluiten resource.
 *
 * Real zaakbesluiten data has not yet been implemented. All data endpoints
 * return 501 Not Implemented so that clients never receive fabricated test
 * data in place of real besluit records.
 *
 * Routes for these endpoints have been removed from appinfo/routes.php until
 * a real implementation backed by ObjectService is in place.
 */
class ZaakBesluitenController extends Controller
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
            ['error' => 'Zaakbesluiten is not yet implemented.'],
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
            ['error' => 'Zaakbesluiten is not yet implemented.'],
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
            ['error' => 'Zaakbesluiten is not yet implemented.'],
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
            ['error' => 'Zaakbesluiten is not yet implemented.'],
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
            ['error' => 'Zaakbesluiten is not yet implemented.'],
            Http::STATUS_NOT_IMPLEMENTED
        );
    }//end destroy()
}//end class
