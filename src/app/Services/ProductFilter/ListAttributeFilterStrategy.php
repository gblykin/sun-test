<?php

declare(strict_types=1);

namespace App\Services\ProductFilter;

use Illuminate\Database\Eloquent\Builder;

class ListAttributeFilterStrategy implements AttributeFilterStrategyInterface
{
    /**
     * Apply filter for LIST type attributes.
     * Filters by attribute_option_id using whereIn.
     *
     * @param Builder $query Query builder for attribute_values table
     * @param int $attributeId Attribute ID
     * @param mixed $value Filter value (array of option IDs or single option ID)
     * @return void
     */
    public function apply(Builder $query, int $attributeId, mixed $value): void
    {
        $query->where('attribute_id', '=', $attributeId);

        // Value can be array of option IDs or single option ID
        $optionIds = is_array($value) ? $value : [$value];
        
        // Filter out null and empty values
        $optionIds = array_filter($optionIds, fn($id) => $id !== null && $id !== '');
        
        if (!empty($optionIds)) {
            $query->whereIn('attribute_option_id', array_map('intval', $optionIds));
        }
    }
}

