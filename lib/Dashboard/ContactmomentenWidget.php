<?php


namespace OCA\ZaakAfhandelApp\Dashboard;

use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Util;

use OCA\ZaakAfhandelApp\AppInfo\Application;

/**
 * Dashboard widget showing contactmomenten (contact moments).
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ContactmomentenWidget implements IWidget
{
    public function __construct(
        private IL10N $l10n,
        private IURLGenerator $url
    ) {
    }//end __construct()

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'zaakAfhandelApp_contactmomenten_widget';
    }//end getId()

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->l10n->t('Contact momenten');
    }//end getTitle()

    /**
     * @inheritDoc
     */
    public function getOrder(): int
    {
        return 10;
    }//end getOrder()

    /**
     * @inheritDoc
     */
    public function getIconClass(): string
    {
        return 'icon-zaken-widget';
    }//end getIconClass()

    /**
     * @inheritDoc
     */
    public function getUrl(): ?string
    {
        return null;
    }//end getUrl()

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess) — OCP\Util::addScript/addStyle is the
     *   Nextcloud-prescribed static API for enqueuing assets in Dashboard widgets.
     */
    public function load(): void
    {
        // Shared chunks emitted by webpack splitChunks + runtimeChunk (see webpack.config.js).
        // Order: runtime → vendor → nc-vue → widget.
        Util::addScript(Application::APP_ID, Application::APP_ID.'-runtime');
        Util::addScript(Application::APP_ID, Application::APP_ID.'-shared-vendor');
        Util::addScript(Application::APP_ID, Application::APP_ID.'-shared-nc-vue');
        Util::addScript(Application::APP_ID, Application::APP_ID.'-contactmomentenWidget');
        Util::addStyle(Application::APP_ID, 'dashboardWidgets');
        Util::addStyle(Application::APP_ID, 'icons');
    }//end load()
}//end class
