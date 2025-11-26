<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Config\AttributeMappingConfig;
use App\Contracts\AttributeMapperInterface;
use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryAttributeMapper implements AttributeMapperInterface
{
    /**
     * Get attribute mapping for a category.
     *
     * @param string $categorySlug Category slug
     * @return array<string, array{slug: string, type: AttributeType, title: string, unit: string|null}>
     */
    public function getMapping(string $categorySlug): array
    {
        return AttributeMappingConfig::getMappingForCategory($categorySlug);
    }

    /**
     * Get or create attribute by slug and type.
     *
     * @param string $slug Attribute slug
     * @param AttributeType $type Attribute type
     * @param string $title Attribute title
     * @param string|null $unit Attribute unit
     * @return int Attribute ID
     */
    public function getOrCreateAttribute(string $slug, AttributeType $type, string $title, ?string $unit = null): int
    {
        $attribute = Attribute::where('slug', '=', $slug)->first();

        if ($attribute === null) {
            $attribute = Attribute::create([
                'slug' => $slug,
                'title' => $title,
                'type_id' => $type->value,
                'unit' => $unit,
            ]);

            Log::info("Created new attribute: {$slug} ({$title})", [
                'attribute_id' => $attribute->id,
                'type' => $type->label(),
            ]);
        }

        return $attribute->id;
    }

    /**
     * Link attribute to category if not already linked.
     *
     * @param int $categoryId Category ID
     * @param int $attributeId Attribute ID
     * @return void
     */
    public function linkAttributeToCategory(int $categoryId, int $attributeId): void
    {
        $exists = DB::table('category_attribute')
            ->where('category_id', '=', $categoryId)
            ->where('attribute_id', '=', $attributeId)
            ->exists();

        if (!$exists) {
            DB::table('category_attribute')->insert([
                'category_id' => $categoryId,
                'attribute_id' => $attributeId,
                'sort_order' => 0,
            ]);

            Log::info("Linked attribute to category", [
                'category_id' => $categoryId,
                'attribute_id' => $attributeId,
            ]);
        }
    }

    /**
     * Get or create attribute option by label.
     *
     * @param int $attributeId Attribute ID
     * @param string $label Option label
     * @return int Attribute option ID
     */
    public function getOrCreateAttributeOption(int $attributeId, string $label): int
    {
        $option = AttributeOption::where('attribute_id', '=', $attributeId)
            ->where('label', '=', $label)
            ->first();

        if ($option === null) {
            // Get max sort_order for this attribute
            $maxSortOrder = AttributeOption::where('attribute_id', '=', $attributeId)
                ->max('sort_order') ?? 0;

            $option = AttributeOption::create([
                'attribute_id' => $attributeId,
                'label' => $label,
                'sort_order' => $maxSortOrder + 1,
            ]);

            Log::info("Created new attribute option", [
                'attribute_id' => $attributeId,
                'option_id' => $option->id,
                'label' => $label,
            ]);
        }

        return $option->id;
    }
}

