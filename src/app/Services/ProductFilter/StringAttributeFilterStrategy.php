<?php

declare(strict_types=1);

namespace App\Services\ProductFilter;

use Illuminate\Database\Eloquent\Builder;

class StringAttributeFilterStrategy implements AttributeFilterStrategyInterface
{
    /**
     * Apply filter for STRING type attributes.
     * Filters by exact match on value_text column.
     *
     * @param Builder $query Query builder for attribute_values table
     * @param int $attributeId Attribute ID
     * @param mixed $value Filter value (string)
     * @return void
     */
    public function apply(Builder $query, int $attributeId, mixed $value): void
    {
        $query->where('attribute_id', '=', $attributeId)
              ->where('value_text', '=', (string) $value);
    }
}

