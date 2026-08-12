<?php

namespace OCA\ZaakAfhandelApp\Service;

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
	 * Get the slug of the DRC (documents) register.
	 *
	 * @return string The register slug
	 */
	public function getDrcRegister(): string {
		return self::REGISTERS['drc'];
	}//end getDrcRegister()

	/**
	 * Get the slug of the BRC (decisions) register.
	 *
	 * @return string The register slug
	 */
	public function getBrcRegister(): string {
		return self::REGISTERS['brc'];
	}//end getBrcRegister()

	/**
	 * Get the slug of the ZRC (cases) register.
	 *
	 * @return string The register slug
	 */
	public function getZrcRegister(): string {
		return self::REGISTERS['zrc'];
	}//end getZrcRegister()

	/**
	 * Get the slug of the ZTC (catalogue) register.
	 *
	 * @return string The register slug
	 */
	public function getZtcRegister(): string {
		return self::REGISTERS['ztc'];
	}//end getZtcRegister()

	/**
	 * Get the slug of the usage rights (gebruiksrechten) schema.
	 *
	 * @return string The schema slug
	 */
	public function getGebruiksrechtenSchema(): string {
		return self::SCHEMAS['gebruiksrechten'];
	}//end getGebruiksrechtenSchema()

	/**
	 * Get the slug of the ZIO (case information object) schema.
	 *
	 * @return string The schema slug
	 */
	public function getZioSchema(): string {
		return self::SCHEMAS['zio'];
	}//end getZioSchema()

	/**
	 * Get the slug of the BIO (decision information object) schema.
	 *
	 * @return string The schema slug
	 */
	public function getBioSchema(): string {
		return self::SCHEMAS['bio'];
	}//end getBioSchema()

	/**
	 * Get the slug of the OIO (object information object) schema.
	 *
	 * @return string The schema slug
	 */
	public function getOioSchema(): string {
		return self::SCHEMAS['oio'];
	}//end getOioSchema()

	/**
	 * Get the slug of the decision (besluit) schema.
	 *
	 * @return string The schema slug
	 */
	public function getBesluitSchema(): string {
		return self::SCHEMAS['besluit'];
	}//end getBesluitSchema()

	/**
	 * Get the slug of the case (zaak) schema.
	 *
	 * @return string The schema slug
	 */
	public function getZaakSchema(): string {
		return self::SCHEMAS['zaak'];
	}//end getZaakSchema()

	/**
	 * Get the slug of the case status schema.
	 *
	 * @return string The schema slug
	 */
	public function getStatusSchema(): string {
		return self::SCHEMAS['status'];
	}//end getStatusSchema()

	/**
	 * Get the slug of the case decision (zaakbesluit) schema.
	 *
	 * @return string The schema slug
	 */
	public function getZaakBesluitSchema(): string {
		return self::SCHEMAS['zaakbesluit'];
	}//end getZaakBesluitSchema()

	/**
	 * Get the slug of the single information object schema.
	 *
	 * @return string The schema slug
	 */
	public function getEnkelvoudigInformatieObjectSchema(): string {
		return self::SCHEMAS['enkelvoudiginformatieobject'];
	}//end getEnkelvoudigInformatieObjectSchema()

	/**
	 * Get the slug of the information object type schema.
	 *
	 * @return string The schema slug
	 */
	public function getIOTSchema(): string {
		return self::SCHEMAS['informatieobjecttype'];
	}//end getIOTSchema()

	/**
	 * Get the slug of the case type to information object type schema.
	 *
	 * @return string The schema slug
	 */
	public function getZTIOTSchema(): string {
		return self::SCHEMAS['zaaktype-informatieobjecttype'];
	}//end getZTIOTSchema()

	/**
	 * Get the slug of the case type (zaaktype) schema.
	 *
	 * @return string The schema slug
	 */
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
