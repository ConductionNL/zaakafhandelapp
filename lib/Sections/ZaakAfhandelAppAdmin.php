<?php

namespace OCA\ZaakAfhandelApp\Sections;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

/**
 * Admin settings section for the ZaakAfhandelApp.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZaakAfhandelAppAdmin implements IIconSection {

	/**
	 * Translation service used for the section display name.
	 *
	 * @var IL10N
	 */
	private IL10N $l;

	/**
	 * URL generator used to resolve the section icon path.
	 *
	 * @var IURLGenerator
	 */
	private IURLGenerator $urlGenerator;

	/**
	 * Constructor.
	 *
	 * @param IL10N $l Translation service used for the section display name.
	 * @param IURLGenerator $urlGenerator URL generator used to resolve the section icon path.
	 */
	public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
	}//end __construct()

	/**
	 * Returns the icon shown next to this section in the admin settings navigation.
	 *
	 * @return string Absolute path to the section icon.
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath('core', 'actions/settings-dark.svg');
	}//end getIcon()

	/**
	 * Returns the identifier of this settings section.
	 *
	 * @return string The section ID, referenced by ISettings::getSection().
	 */
	public function getID(): string {
		return 'zaakafhandelapp';
	}//end getID()

	/**
	 * Returns the human-readable name of this settings section.
	 *
	 * @return string The translated section name.
	 */
	public function getName(): string {
		return $this->l->t('Zaak Afhandelapp');
	}//end getName()

	/**
	 * Returns the sort priority of this section in the admin settings navigation.
	 *
	 * @return int The section priority; lower values sort higher.
	 */
	public function getPriority(): int {
		return 97;
	}//end getPriority()
}//end class
