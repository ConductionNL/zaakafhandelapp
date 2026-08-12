<?php

namespace OCA\ZaakAfhandelApp\Dashboard;

use OCA\ZaakAfhandelApp\AppInfo\Application;
use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * Dashboard widget showing organisaties (organisations).
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class OrganisatiesWidget implements IWidget {
	/**
	 * Constructor.
	 *
	 * @param IL10N $l10n Translation service used for the widget title.
	 * @param IURLGenerator $url URL generator for building links to app routes.
	 */
	public function __construct(
		private IL10N $l10n,
		private IURLGenerator $url,
	) {
	}//end __construct()

	/**
	 * @inheritDoc
	 *
	 * @return string The unique dashboard widget identifier.
	 */
	public function getId(): string {
		return 'zaakAfhandelApp_organisaties_widget';
	}//end getId()

	/**
	 * @inheritDoc
	 *
	 * @return string The translated widget title shown on the dashboard.
	 */
	public function getTitle(): string {
		return $this->l10n->t('Organisaties zoeken');
	}//end getTitle()

	/**
	 * @inheritDoc
	 *
	 * @return int The sort order of this widget on the dashboard.
	 */
	public function getOrder(): int {
		return 10;
	}//end getOrder()

	/**
	 * @inheritDoc
	 *
	 * @return string The CSS class rendering the widget icon.
	 */
	public function getIconClass(): string {
		return 'icon-zaken-widget';
	}//end getIconClass()

	/**
	 * @inheritDoc
	 *
	 * @return string|null The "more" link target, or null when the widget has none.
	 */
	public function getUrl(): ?string {
		return null;
	}//end getUrl()

	/**
	 * @inheritDoc
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess) — OCP\Util::addScript/addStyle is the
	 *   Nextcloud-prescribed static API for enqueuing assets in Dashboard widgets.
	 *
	 * @return void
	 */
	public function load(): void {
		// Shared chunks emitted by webpack splitChunks + runtimeChunk (see webpack.config.js).
		// Order: runtime → vendor → nc-vue → widget.
		Util::addScript(Application::APP_ID, Application::APP_ID . '-runtime');
		Util::addScript(Application::APP_ID, Application::APP_ID . '-shared-vendor');
		Util::addScript(Application::APP_ID, Application::APP_ID . '-shared-nc-vue');
		Util::addScript(Application::APP_ID, Application::APP_ID . '-organisatiesWidget');
		Util::addStyle(Application::APP_ID, 'dashboardWidgets');
		Util::addStyle(Application::APP_ID, 'icons');
	}//end load()
}//end class
