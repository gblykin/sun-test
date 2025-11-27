<?php

declare(strict_types=1);

namespace App\Http\Requests\Parsers;

class NumericAttributeValueParser implements AttributeValueParserInterface
{
    /**
     * Parse DECIMAL/INTEGER type attribute value.
     * Format: range format (e.g., "400:600", ":600", "400:") or single value
     *
     * @param string $value Range or single numeric value
     * @return array{0: float|null, 1: float|null}|float|null Parsed value
     */
    public function parse(string $value): array|float|null
    {
        if (strpos($value, ':') !== false) {
            // Range format: "min:max", ":max", "min:"
            [$min, $max] = explode(':', $value, 2);
            $min = $this->parseNumericValue(trim($min));
            $max = $this->parseNumericValue(trim($max));
            
            // Return array only if at least one value is set
            if ($min !== null || $max !== null) {
                return [$min, $max];
            }
            
            return null;
        }
        
        // Single value
        return $this->parseNumericValue($value);
    }

    /**
     * Parse numeric value from string.
     *
     * @param string $value
     * @return float|null
     */
    private function parseNumericValue(string $value): ?float
    {
        if ($value === '') {
            return null;
        }
        
        return (float) $value;
    }
}

