<?php

/**
 * Test stub for OCA\OpenRegister\Db\ObjectEntity.
 *
 * Fallback used only when the real OpenRegister app is NOT loaded (standalone /
 * CI without the app installed). Unlike the service stubs this is a concrete,
 * functional implementation: the ZGW services read and write entity state via
 * setObject()/jsonSerialize()/getRegister()/getSchema(), so the tests need a
 * real working entity (not a mock). The behaviour mirrors the subset of the
 * real OpenRegister ObjectEntity the tests rely on: jsonSerialize() returns the
 * object payload, and setObject() replaces it. When the real OpenRegister
 * runtime is present this file is never autoloaded.
 *
 * @category Tests
 * @package  OCA\ZaakAfhandelApp\Tests
 *
 * @author    Conduction Development Team <info@conduction.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @link https://github.com/ConductionNL/zaakafhandelapp
 */

declare(strict_types=1);

namespace OCA\OpenRegister\Db;

use JsonSerializable;

/**
 * Concrete stub for ObjectEntity supporting the accessors the tests use.
 */
class ObjectEntity implements JsonSerializable
{

    /**
     * @var array<string,mixed> The object payload.
     */
    private array $object = [];

    /**
     * @var string|null The register id/slug.
     */
    private ?string $register = null;

    /**
     * @var string|null The schema id/slug.
     */
    private ?string $schema = null;

    /**
     * @var string|null The object uuid.
     */
    private ?string $uuid = null;

    /**
     * Replace the object payload.
     *
     * @param array<string,mixed> $object The new payload.
     *
     * @return void
     */
    public function setObject(array $object): void
    {
        $this->object = $object;
    }//end setObject()

    /**
     * Return the raw object payload.
     *
     * @return array<string,mixed>
     */
    public function getObject(): array
    {
        return $this->object;
    }//end getObject()

    /**
     * Set the register scope.
     *
     * @param string|null $register The register id/slug.
     *
     * @return void
     */
    public function setRegister(?string $register): void
    {
        $this->register = $register;
    }//end setRegister()

    /**
     * Get the register scope.
     *
     * @return string|null
     */
    public function getRegister(): ?string
    {
        return $this->register;
    }//end getRegister()

    /**
     * Set the schema scope.
     *
     * @param string|null $schema The schema id/slug.
     *
     * @return void
     */
    public function setSchema(?string $schema): void
    {
        $this->schema = $schema;
    }//end setSchema()

    /**
     * Get the schema scope.
     *
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }//end getSchema()

    /**
     * Set the object uuid.
     *
     * @param string|null $uuid The uuid.
     *
     * @return void
     */
    public function setUuid(?string $uuid): void
    {
        $this->uuid = $uuid;
    }//end setUuid()

    /**
     * Get the object uuid.
     *
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }//end getUuid()

    /**
     * Serialize the entity to the object payload.
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->object;
    }//end jsonSerialize()
}//end class
