<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Enums\AttributeType;

interface AttributeMapperInterface
{
    /**
     * Get attribute mapping for a category.
     *
     * @param string $categorySlug Category slug
     * @return array<string, array{slug: string, type: AttributeType}> Mapping of CSV column to attribute config
     */
    public function getMapping(string $categorySlug): array;

    /**
     * Get or create attribute by slug and type.
     *
     * @param string $slug Attribute slug
     * @param AttributeType $type Attribute type
     * @param string $title Attribute title
     * @param string|null $unit Attribute unit
     * @return int Attribute ID
     */
    public function getOrCreateAttribute(string $slug, AttributeType $type, string $title, ?string $unit = null): int;

    /**
     * Link attribute to category if not already linked.
     *
     * @param int $categoryId Category ID
     * @param int $attributeId Attribute ID
     * @return void
     */
    public function linkAttributeToCategory(int $categoryId, int $attributeId): void;

    /**
     * Get or create attribute option by label.
     *
     * @param int $attributeId Attribute ID
     * @param string $label Option label
     * @return int Attribute option ID
     */
    public function getOrCreateAttributeOption(int $attributeId, string $label): int;
}

