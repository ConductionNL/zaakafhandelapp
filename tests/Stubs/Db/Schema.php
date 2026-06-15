<?php

/**
 * Test stub for OCA\OpenRegister\Db\Schema.
 *
 * Fallback used only when the real OpenRegister app is NOT loaded. Concrete so
 * the tests can build a real Schema with a slug for ZGWLogicService routing.
 * No-op when the real class exists.
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

/**
 * Stub for Schema supporting the slug accessor the tests use.
 */
class Schema
{

    /**
     * @var string|null The schema slug.
     */
    private ?string $slug = null;

    /**
     * Set the schema slug.
     *
     * @param string|null $slug The slug.
     *
     * @return void
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }//end setSlug()

    /**
     * Get the schema slug.
     *
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }//end getSlug()
}//end class
