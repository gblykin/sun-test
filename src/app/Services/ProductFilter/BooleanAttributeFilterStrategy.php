<?php

declare(strict_types=1);

namespace App\Services\ProductFilter;

use Illuminate\Database\Eloquent\Builder;

class BooleanAttributeFilterStrategy implements AttributeFilterStrategyInterface
{
    /**
     * Apply filter for BOOLEAN type attributes.
     * Normalizes boolean values and filters by exact match on value_decimal column.
     *
     * @param Builder $query Query builder for attribute_values table
     * @param int $attributeId Attribute ID
     * @param mixed $value Filter value (string representation of boolean)
     * @return void
     */
    public function apply(Builder $query, int $attributeId, mixed $value): void
    {
        $query->where('attribute_id', '=', $attributeId);
        
        // Normalize boolean value to 1.0 or 0.0
        $normalizedValue = $this->normalizeBooleanToDecimal($value);
        
        $query->where('value_decimal', '=', $normalizedValue);
    }

    /**
     * Normalize boolean value to decimal (1.0 for true, 0.0 for false).
     * Handles various representations: "1", "0", "true", "false", "yes", "no", etc.
     *
     * @param mixed $value
     * @return float 1.0 or 0.0
     */
    private function normalizeBooleanToDecimal(mixed $value): float
    {
        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }

        $normalized = strtolower(trim((string) $value));
        
        return match ($normalized) {
            '1', 'true', 'yes', 'on', 'y' => 1.0,
            '0', 'false', 'no', 'off', 'n' => 0.0,
            default => (float) $value, // Try to cast if not recognized
        };
    }
}

