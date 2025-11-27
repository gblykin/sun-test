<?php

declare(strict_types=1);

namespace App\Services\ProductFilter;

use Illuminate\Database\Eloquent\Builder;

class NumericAttributeFilterStrategy implements AttributeFilterStrategyInterface
{
    /**
     * Apply filter for DECIMAL and INTEGER type attributes.
     * Supports range filtering (min:max) or exact value matching.
     *
     * @param Builder $query Query builder for attribute_values table
     * @param int $attributeId Attribute ID
     * @param mixed $value Filter value (array [min, max] or single numeric value)
     * @return void
     */
    public function apply(Builder $query, int $attributeId, mixed $value): void
    {
        $query->where('attribute_id', '=', $attributeId);

        if (is_array($value) && count($value) === 2) {
            // Range: [min, max] - handle null values
            $min = $this->parseNumericValue($value[0]);
            $max = $this->parseNumericValue($value[1]);
            
            if ($min !== null && $max !== null) {
                $query->whereBetween('value_decimal', [$min, $max]);
            } elseif ($min !== null) {
                $query->where('value_decimal', '>=', $min);
            } elseif ($max !== null) {
                $query->where('value_decimal', '<=', $max);
            }
        } else {
            // Exact value
            $query->where('value_decimal', '=', (float) $value);
        }
    }

    /**
     * Parse numeric value, handling null and empty strings.
     *
     * @param mixed $value
     * @return float|null
     */
    private function parseNumericValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        return (float) $value;
    }
}

