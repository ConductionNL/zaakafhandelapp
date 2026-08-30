<?php

/**
 * Wire-contract tests for `GET /me` (users#me).
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests\Unit\Controller
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: 2026 Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Tests\Unit\Controller;

use OCA\ZaakAfhandelApp\Controller\UsersController;
use OCP\AppFramework\Http;
use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

/**
 * Locks the wire contract of the current-user endpoint.
 *
 * `me()` reads the SESSION and nothing else — it takes no id, so there is no
 * object to scope and no way to ask it about somebody else. That is the whole
 * shape of the endpoint, and it is what these tests pin:
 *
 *   1. anonymous → 401, and nothing about any user in the body;
 *   2. authenticated → 200 with the CALLER's own uid under `user.id`.
 *
 * Arm 2 asserts the identity, not just the status. An endpoint that answered
 * 200 with a hardcoded or stale user would satisfy a status-only test while
 * telling the SPA it is logged in as the wrong person.
 *
 * @spec openspec/specs/app-configuration/spec.md#REQ-004
 */
class UsersControllerTest extends TestCase {
	/**
	 * Build the controller over a session holding the given user (or none).
	 *
	 * @param IUser|null $user The signed-in user, or null for an anonymous call.
	 *
	 * @return UsersController
	 */
	private function controller(?IUser $user): UsersController {
		$session = $this->createMock(IUserSession::class);
		$session->method('getUser')->willReturn($user);

		return new UsersController(
			'zaakafhandelapp',
			$this->createMock(IRequest::class),
			$this->createMock(IAppConfig::class),
			$session
		);
	}//end controller()

	/**
	 * A fully-stubbed IUser, so `me()` can serialise every field it reads.
	 *
	 * @param string $uid The uid the session resolves.
	 *
	 * @return IUser
	 */
	private function user(string $uid): IUser {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$user->method('getDisplayName')->willReturn('Case Handler');
		$user->method('getEMailAddress')->willReturn($uid . '@example.org');
		$user->method('getSystemEMailAddress')->willReturn($uid . '@example.org');
		$user->method('getPrimaryEMailAddress')->willReturn($uid . '@example.org');
		$user->method('getLastLogin')->willReturn(0);
		$user->method('getQuota')->willReturn('none');
		$user->method('getHome')->willReturn('/data/' . $uid);
		$user->method('getBackendClassName')->willReturn('Database');
		$user->method('getAvatarImage')->willReturn(null);
		$user->method('getCloudId')->willReturn($uid . '@localhost');
		$user->method('isEnabled')->willReturn(true);
		$user->method('canChangeDisplayName')->willReturn(true);
		$user->method('canChangePassword')->willReturn(true);
		$user->method('canChangeAvatar')->willReturn(true);
		$user->method('getManagerUids')->willReturn([]);

		return $user;
	}//end user()

	/**
	 * An anonymous caller is refused, and learns nothing about any user.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/app-configuration/spec.md#REQ-004
	 */
	public function testAnonymousCallerIsRefused(): void {
		$response = $this->controller(null)->me();

		$this->assertSame(Http::STATUS_UNAUTHORIZED, $response->getStatus());
		$this->assertArrayNotHasKey('user', $response->getData());
	}//end testAnonymousCallerIsRefused()

	/**
	 * An authenticated caller gets THEIR OWN identity back.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/app-configuration/spec.md#REQ-004
	 */
	public function testAuthenticatedCallerGetsTheirOwnIdentity(): void {
		$response = $this->controller($this->user('alice'))->me();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$data = $response->getData();
		$this->assertSame('alice', $data['user']['id']);
		$this->assertSame('Case Handler', $data['user']['displayName']);
	}//end testAuthenticatedCallerGetsTheirOwnIdentity()

	/**
	 * The endpoint follows the SESSION, not a remembered first caller.
	 *
	 * Two controllers, two sessions, two different answers. Without this a
	 * static or memoised lookup would pass both tests above.
	 *
	 * @return void
	 *
	 * @spec openspec/specs/app-configuration/spec.md#REQ-004
	 */
	public function testIdentityFollowsTheSession(): void {
		$alice = $this->controller($this->user('alice'))->me()->getData();
		$bob = $this->controller($this->user('bob'))->me()->getData();

		$this->assertSame('alice', $alice['user']['id']);
		$this->assertSame('bob', $bob['user']['id']);
	}//end testIdentityFollowsTheSession()
}//end class
