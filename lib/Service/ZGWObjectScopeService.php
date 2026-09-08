<?php

/**
 * ZaakAfhandelApp ZGW object-ownership resolver.
 *
 * @category  Service
 * @package   OCA\ZaakAfhandelApp\Service
 * @author    Conduction b.v. <info@conduction.nl>
 * @copyright 2026 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * SPDX-FileCopyrightText: Conduction B.V. <info@conduction.nl>
 * SPDX-License-Identifier: EUPL-1.2
 */

declare(strict_types=1);

namespace OCA\ZaakAfhandelApp\Service;

use OCA\OpenRegister\Db\ObjectEntity;
use OCA\OpenRegister\Db\Register;
use OCA\OpenRegister\Db\RegisterMapper;
use OCA\OpenRegister\Db\Schema;
use OCA\OpenRegister\Db\SchemaMapper;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Answers one question: is this written object ours?
 *
 * Split out of ZGWZaakEventHandler, which routes a schema to the ZGW rules it
 * triggers. That routing is a different concern from deciding whether the object
 * is in this app's scope at all, and the two were conflated: every handler
 * dispatched on the schema SLUG alone, so any app on the same OpenRegister
 * instance that named a schema `zaak`, `status`, `besluit` or
 * `zaakinformatieobject` had this app's ZGW cascade run against its data.
 */
class ZGWObjectScopeService {
	public function __construct(
		private readonly ZGWRegistryService $registry,
		private readonly SchemaMapper $schemaMapper,
		private readonly RegisterMapper $registerMapper,
		private readonly LoggerInterface $logger,
	) {
	}//end __construct()

	/**
	 * The schema slug of an object this app owns, or null when it is another app's.
	 *
	 * @param ObjectEntity $obj The object that was written.
	 *
	 * @return string|null The schema slug, or null when the write is out of scope.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	public function ownedSlugOf(ObjectEntity $obj): ?string {
		return $this->ownedSlugOfSchema($obj, $this->schemaMapper->find($obj->getSchema()));
	}//end ownedSlugOf()

	/**
	 * The same decision, for a caller that already resolved the schema.
	 *
	 * @param ObjectEntity $obj The object that was written.
	 * @param Schema $schema The schema it belongs to.
	 *
	 * @return string|null The schema slug, or null when the write is out of scope.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	public function ownedSlugOfSchema(ObjectEntity $obj, Schema $schema): ?string {
		if ($this->ownsObject($obj, $schema) === false) {
			$this->logger->debug(
				'ZaakAfhandelApp: skipping ZGW rules for an object owned by another app',
				[
					'object' => $obj->getUuid(),
					'register' => $obj->getRegister(),
					'schema' => $obj->getSchema(),
					'schemaSlug' => $schema->getSlug(),
				]
			);

			return null;
		}

		return (string)$schema->getSlug();
	}//end ownedSlugOfSchema()

	/**
	 * Whether an object written elsewhere on this instance is one of ours.
	 *
	 * The schema SLUG alone does not answer this. `zaak`, `status`, `besluit` and
	 * `zaakinformatieobject` are ordinary domain words, and any app on the same
	 * OpenRegister instance may name a schema of its own after one of them —
	 * dossiq ships `zaakinformatieobject`, `besluitinformatieobject` and
	 * `zaaktypeInformatieobjecttype` in its own register today. Dispatching on the
	 * slug ran this app's ZGW cascade against that app's data, which 500s the
	 * foreign write.
	 *
	 * The decision uses OpenRegister's own ownership record, in two steps:
	 *
	 * 1. An object in one of the four canonical ZGW registers is ours by
	 *    definition — those are the registers the cascade itself reads and writes.
	 * 2. Otherwise it is skipped only when OpenRegister can PROVE another app owns
	 *    the register or the schema. An entity with no application stamp (a
	 *    register an administrator created by hand — how the ZGW registers exist on
	 *    most instances) stays in scope, so the narrowing cannot take a working
	 *    deployment dark.
	 *
	 * @param ObjectEntity $obj The object that was written.
	 * @param Schema $schema The schema it belongs to.
	 *
	 * @return boolean True when this app's ZGW rules apply to the object.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	private function ownsObject(ObjectEntity $obj, Schema $schema): bool {
		$register = $this->registerOf($obj);

		if ($register !== null && $this->registry->isZgwRegisterSlug($register->getSlug()) === true) {
			return true;
		}

		if ($register !== null && $this->registry->isForeignApplication($register->getApplication()) === true) {
			return false;
		}

		return $this->registry->isForeignApplication($schema->getApplication()) === false;
	}//end ownsObject()

	/**
	 * The register an object belongs to, or null when it cannot be resolved.
	 *
	 * An unresolvable register is not treated as foreign: the caller falls back to
	 * the schema's own application stamp rather than silently dropping the write.
	 *
	 * @param ObjectEntity $obj The object that was written.
	 *
	 * @return Register|null The register, or null when the lookup fails.
	 *
	 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-008
	 */
	private function registerOf(ObjectEntity $obj): ?Register {
		$id = $obj->getRegister();
		if ($id === null || $id === '') {
			return null;
		}

		try {
			return $this->registerMapper->find($id);
		} catch (Throwable $e) {
			$this->logger->warning(
				'ZaakAfhandelApp: could not resolve the register of a written object',
				['register' => $id, 'exception' => $e->getMessage()]
			);

			return null;
		}
	}//end registerOf()
}//end class
