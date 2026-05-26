<?php

namespace OCA\ZaakAfhandelApp\Controller;

use OCP\IAppConfig;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUserSession;

/**
 * Class UsersController
 *
 * Controller for handling user-related operations in the ZaakAfhandelApp.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */
class UsersController extends Controller
{
    /**
     * UsersController constructor.
     *
     * @param string       $appName     The name of the app
     * @param IAppConfig   $config      The app configuration
     * @param IRequest     $request     The request object
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
     * Gets info about the currently active/logged-in user.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse Info about the current user.
      *
      * @spec openspec/specs/app-configuration/spec.md#REQ-004
     */
    public function me(): JSONResponse
    {
        // Get the current user
        $currentUser = $this->userSession->getUser();
        if ($currentUser === null) {
            return new JSONResponse(['error' => 'Not authenticated'], Http::STATUS_UNAUTHORIZED);
        }

        try {
            $data = [
                'user'       => [
                    'id'                   => $currentUser->getUID(),
                    'displayName'          => $currentUser->getDisplayName(),
                    'email'                => $currentUser->getEMailAddress(),
                    'systemEmail'          => $currentUser->getSystemEMailAddress(),
                    'primaryEmail'         => $currentUser->getPrimaryEMailAddress(),
                    'lastLogin'            => $currentUser->getLastLogin(),
                    'quota'                => $currentUser->getQuota(),
                    'home'                 => $currentUser->getHome(),
                    'backendClassName'     => $currentUser->getBackendClassName(),
                    'avatarImage'          => $currentUser->getAvatarImage(64),
                    'cloudId'              => $currentUser->getCloudId(),
                    'isEnabled'            => $currentUser->isEnabled(),
                    'canChangeDisplayName' => $currentUser->canChangeDisplayName(),
                    'canChangePassword'    => $currentUser->canChangePassword(),
                    'canChangeAvatar'      => $currentUser->canChangeAvatar(),
                    'managerUids'          => $currentUser->getManagerUids(),
                ],
                'medewerker' => 'placeholder-todo',
            ];
            return new JSONResponse($data);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }//end try
    }//end me()
}//end class
