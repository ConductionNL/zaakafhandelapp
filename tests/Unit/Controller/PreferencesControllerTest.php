<?php

/**
 * Wire-contract tests for the per-user preferences endpoints.
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

use OCA\ZaakAfhandelApp\Controller\PreferencesController;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Locks the wire contract of the generic preferences endpoints:
 *
 *   GET /api/preferences/{key}  -> preferences#getPreference
 *   PUT /api/preferences/{key}  -> preferences#setPreference
 *
 * Both are #[NoAdminRequired] and both take the storage key straight off the
 * URL, which makes them a read/write primitive over IConfig user values aimed
 * by the caller. Two properties keep that from being an arbitrary-config
 * read/write for any authenticated user, and both are asserted here against
 * the exact key the config is addressed with:
 *
 *   1. every key is namespaced with the literal `pref_` prefix, so no request
 *      can reach a user value the app did not write;
 *   2. the key is reduced to [a-z0-9-] first, so `../` or an app-name-shaped
 *      key cannot survive into the lookup.
 *
 * The response envelope is `{value: string|null}` in every case — including
 * "absent" and "cleared", which the shared @conduction/nextcloud-vue widgets
 * read as null rather than "". That is why the null cases are pinned as null
 * and not merely as falsy.
 */
class PreferencesControllerTest extends TestCase
{

    /**
     * The uid the mocked session resolves to.
     *
     * @var string
     */
    private const UID = 'alice';

    /**
     * @var IConfig&MockObject
     */
    private $config;


    protected function setUp(): void
    {
        $this->config = $this->createMock(IConfig::class);

    }//end setUp()


    /**
     * Build the controller under test.
     *
     * @param bool $authenticated Whether IUserSession returns a user.
     *
     * @return PreferencesController The controller under test.
     */
    private function makeController(bool $authenticated=true): PreferencesController
    {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn(self::UID);

        $session = $this->createMock(IUserSession::class);
        $session->method('getUser')->willReturn($authenticated === true ? $user : null);

        return new PreferencesController($this->createMock(IRequest::class), $this->config, $session);

    }//end makeController()


    /**
     * A stored value comes back under `value`, read from the `pref_`-namespaced
     * key inside this app's config space.
     *
     * @return void
     */
    public function testGetReturnsTheStoredValueFromTheNamespacedKey(): void
    {
        $this->config->expects($this->once())
            ->method('getUserValue')
            ->with(self::UID, 'zaakafhandelapp', 'pref_support-seen', '')
            ->willReturn('yes');

        $response = $this->makeController()->getPreference('support-seen');

        $this->assertSame(Http::STATUS_OK, $response->getStatus());
        $this->assertSame(['value' => 'yes'], $response->getData());
    }//end testGetReturnsTheStoredValueFromTheNamespacedKey()


    /**
     * An unset preference answers 200 with an explicit null — not "", and not
     * a 404. The widgets distinguish "never set" from "set to empty".
     *
     * @return void
     */
    public function testGetReturnsNullForAnUnsetPreference(): void
    {
        $this->config->method('getUserValue')->willReturn('');

        $response = $this->makeController()->getPreference('support-seen');

        $this->assertSame(Http::STATUS_OK, $response->getStatus());
        $this->assertArrayHasKey('value', $response->getData());
        $this->assertNull($response->getData()['value']);
    }//end testGetReturnsNullForAnUnsetPreference()


    /**
     * The key is lower-cased and stripped to [a-z0-9-] before it is used, so a
     * traversal- or namespace-shaped key cannot address another app's value.
     *
     * @return void
     */
    public function testGetSanitisesTheKeyBeforeAddressingTheConfig(): void
    {
        $this->config->expects($this->once())
            ->method('getUserValue')
            ->with(self::UID, 'zaakafhandelapp', 'pref_supportseen', '')
            ->willReturn('');

        $this->makeController()->getPreference('../Support_Seen!');
    }//end testGetSanitisesTheKeyBeforeAddressingTheConfig()


    /**
     * A key with nothing safe left in it is refused with 400 and never reaches
     * the config.
     *
     * @return void
     */
    public function testGetRejectsAKeyThatSanitisesToNothing(): void
    {
        $this->config->expects($this->never())->method('getUserValue');

        $response = $this->makeController()->getPreference('___');

        $this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
        $this->assertArrayHasKey('message', $response->getData());
    }//end testGetRejectsAKeyThatSanitisesToNothing()


    /**
     * An unauthenticated read answers 401 and never touches the config.
     *
     * @return void
     */
    public function testGetUnauthenticatedAnswers401(): void
    {
        $this->config->expects($this->never())->method('getUserValue');

        $this->assertSame(
            Http::STATUS_UNAUTHORIZED,
            $this->makeController(false)->getPreference('support-seen')->getStatus()
        );
    }//end testGetUnauthenticatedAnswers401()


    /**
     * A write persists to the namespaced key and echoes the stored value back.
     *
     * @return void
     */
    public function testSetPersistsToTheNamespacedKeyAndEchoesTheValue(): void
    {
        $this->config->expects($this->once())
            ->method('setUserValue')
            ->with(self::UID, 'zaakafhandelapp', 'pref_support-seen', 'yes');
        $this->config->expects($this->never())->method('deleteUserValue');

        $response = $this->makeController()->setPreference('support-seen', 'yes');

        $this->assertSame(Http::STATUS_OK, $response->getStatus());
        $this->assertSame(['value' => 'yes'], $response->getData());
    }//end testSetPersistsToTheNamespacedKeyAndEchoesTheValue()


    /**
     * An empty value CLEARS the preference — it must delete the user value
     * rather than store "", and answer `{value: null}`. Storing "" would make
     * the next read indistinguishable from "never set" only by accident.
     *
     * @return void
     */
    public function testSetWithAnEmptyValueDeletesRatherThanStoringEmpty(): void
    {
        $this->config->expects($this->once())
            ->method('deleteUserValue')
            ->with(self::UID, 'zaakafhandelapp', 'pref_support-seen');
        $this->config->expects($this->never())->method('setUserValue');

        $response = $this->makeController()->setPreference('support-seen', '');

        $this->assertSame(Http::STATUS_OK, $response->getStatus());
        $this->assertArrayHasKey('value', $response->getData());
        $this->assertNull($response->getData()['value']);
    }//end testSetWithAnEmptyValueDeletesRatherThanStoringEmpty()


    /**
     * A key that sanitises to nothing is refused with 400 and nothing is
     * written or deleted.
     *
     * @return void
     */
    public function testSetRejectsAKeyThatSanitisesToNothing(): void
    {
        $this->config->expects($this->never())->method('setUserValue');
        $this->config->expects($this->never())->method('deleteUserValue');

        $response = $this->makeController()->setPreference('___', 'yes');

        $this->assertSame(Http::STATUS_BAD_REQUEST, $response->getStatus());
        $this->assertArrayHasKey('message', $response->getData());
    }//end testSetRejectsAKeyThatSanitisesToNothing()


    /**
     * An unauthenticated write answers 401 and persists nothing.
     *
     * @return void
     */
    public function testSetUnauthenticatedAnswers401AndPersistsNothing(): void
    {
        $this->config->expects($this->never())->method('setUserValue');
        $this->config->expects($this->never())->method('deleteUserValue');

        $this->assertSame(
            Http::STATUS_UNAUTHORIZED,
            $this->makeController(false)->setPreference('support-seen', 'yes')->getStatus()
        );
    }//end testSetUnauthenticatedAnswers401AndPersistsNothing()
}//end class
