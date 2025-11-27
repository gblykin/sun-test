<?php

declare(strict_types=1);

namespace App\Http\Requests\Parsers;

class BooleanAttributeValueParser implements AttributeValueParserInterface
{
    /**
     * Parse BOOLEAN type attribute value.
     * Converts various string representations to normalized boolean decimal.
     * Accepts: "1", "0", "true", "false", "yes", "no", "on", "off"
     *
     * @param string $value Boolean value as string
     * @return float Normalized boolean value (1.0 or 0.0)
     */
    public function parse(string $value): float
    {
        $normalized = strtolower(trim($value));
        
        // Convert various representations to 1.0 or 0.0
        return match ($normalized) {
            '1', 'true', 'yes', 'on', 'y' => 1.0,
            '0', 'false', 'no', 'off', 'n' => 0.0,
            default => (float) $value, // Try to cast if not recognized
        };
    }
}

