<?php

namespace OCA\ZaakAfhandelApp\Service;

use OCA\ZaakAfhandelApp\AppInfo\Application;

/**
 * Service for ZGW registry and schema slug lookups.
 *
 * Provides a centralized place for register and schema identifiers
 * used across ZGW services.
 *
 * @copyright 2024 Conduction B.V. <info@conduction.nl>
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */
class ZGWRegistryService {

	/**
	 * Register slug mappings.
	 */
	private const REGISTERS = [
		'drc' => 'documenten',
		'brc' => 'besluiten',
		'zrc' => 'zaken',
		'ztc' => 'catalogi',
	];

	/**
	 * Schema slug mappings.
	 */
	private const SCHEMAS = [
		'zio' => 'zaakinformatieobject',
		'bio' => 'besluitinformatieobject',
		'oio' => 'objectinformatieobject',
		'besluit' => 'besluit',
		'zaak' => 'zaak',
		'status' => 'status',
		'gebruiksrechten' => 'gebruiksrechten',
		'zaakbesluit' => 'zaakbesluit',
		'enkelvoudiginformatieobject' => 'enkelvoudiginformatieobject',
		'informatieobjecttype' => 'informatieobjecttype',
		'zaaktype-informatieobjecttype' => 'zaaktypeinformatieobjecttype',
		'zaaktype' => 'zaaktype',
	];

	/**
	 * The app that provisions the ZGW registers and schemas described above.
	 *
	 * OpenRegister stamps the importing app id onto every register and schema it
	 * imports (`Register::getApplication()` / `Schema::getApplication()`), which
	 * is what lets this app tell its own `zaak` from another app's `zaak`.
	 */
	private const OWNING_APPLICATION = Application::APP_ID;

	/**
	 * Whether a register slug is one of the four canonical ZGW registers.
	 *
	 * OpenRegister matches slugs case-insensitively, so this does too.
	 *
	 * @param string|null $slug The register slug to test.
	 *
	 * @return boolean True when the slug names a ZGW register.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	public function isZgwRegisterSlug(?string $slug): bool {
		if ($slug === null || $slug === '') {
			return false;
		}

		foreach (self::REGISTERS as $registerSlug) {
			if (strcasecmp($slug, $registerSlug) === 0) {
				return true;
			}
		}

		return false;
	}//end isZgwRegisterSlug()

	/**
	 * Whether an OpenRegister `application` stamp proves another app owns the entity.
	 *
	 * Deliberately one-directional. An empty stamp is NOT foreign: a register an
	 * administrator created by hand carries no application at all, and the ZGW
	 * rules have to keep running on those instances. Only a stamp naming a
	 * different app is treated as proof, so the narrowing can never take a
	 * working deployment dark.
	 *
	 * Seeded fixtures are stamped with variants such as `openregister.mock` and
	 * `planninq.demo`, so the comparison uses the app id ahead of the first dot.
	 *
	 * @param string|null $application The application stamp to test.
	 *
	 * @return boolean True when the stamp names an app other than this one.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	public function isForeignApplication(?string $application): bool {
		$stamp = trim((string)$application);
		if ($stamp === '') {
			return false;
		}

		$appId = explode('.', $stamp)[0];

		return strcasecmp($appId, self::OWNING_APPLICATION) !== 0;
	}//end isForeignApplication()

	public function getDrcRegister(): string {
		return self::REGISTERS['drc'];
	}//end getDrcRegister()

	public function getBrcRegister(): string {
		return self::REGISTERS['brc'];
	}//end getBrcRegister()

	public function getZrcRegister(): string {
		return self::REGISTERS['zrc'];
	}//end getZrcRegister()

	public function getZtcRegister(): string {
		return self::REGISTERS['ztc'];
	}//end getZtcRegister()

	public function getGebruiksrechtenSchema(): string {
		return self::SCHEMAS['gebruiksrechten'];
	}//end getGebruiksrechtenSchema()

	public function getZioSchema(): string {
		return self::SCHEMAS['zio'];
	}//end getZioSchema()

	public function getBioSchema(): string {
		return self::SCHEMAS['bio'];
	}//end getBioSchema()

	public function getOioSchema(): string {
		return self::SCHEMAS['oio'];
	}//end getOioSchema()

	public function getBesluitSchema(): string {
		return self::SCHEMAS['besluit'];
	}//end getBesluitSchema()

	public function getZaakSchema(): string {
		return self::SCHEMAS['zaak'];
	}//end getZaakSchema()

	public function getStatusSchema(): string {
		return self::SCHEMAS['status'];
	}//end getStatusSchema()

	public function getZaakBesluitSchema(): string {
		return self::SCHEMAS['zaakbesluit'];
	}//end getZaakBesluitSchema()

	public function getEnkelvoudigInformatieObjectSchema(): string {
		return self::SCHEMAS['enkelvoudiginformatieobject'];
	}//end getEnkelvoudigInformatieObjectSchema()

	public function getIOTSchema(): string {
		return self::SCHEMAS['informatieobjecttype'];
	}//end getIOTSchema()

	public function getZTIOTSchema(): string {
		return self::SCHEMAS['zaaktype-informatieobjecttype'];
	}//end getZTIOTSchema()

	public function getZaakTypeSchema(): string {
		return self::SCHEMAS['zaaktype'];
	}//end getZaakTypeSchema()

	/**
	 * Extract an object ID from an endpoint URL.
	 *
	 * @param string $url The endpoint URL
	 *
	 * @return string The extracted object ID
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-001
	 */
	public function getObjectIdByEndpointUrl(string $url): string {
		$explodedUrl = explode('/', $url);
		return end($explodedUrl);
	}//end getObjectIdByEndpointUrl()
}//end class
