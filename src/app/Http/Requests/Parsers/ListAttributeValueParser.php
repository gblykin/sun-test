<?php

declare(strict_types=1);

namespace App\Http\Requests\Parsers;

class ListAttributeValueParser implements AttributeValueParserInterface
{
    /**
     * Parse LIST type attribute value.
     * Format: comma-separated values (e.g., "1,2,3")
     *
     * @param string $value Comma-separated option IDs
     * @return array<int>|null Array of option IDs or null if empty
     */
    public function parse(string $value): array|null
    {
        $values = array_map('trim', explode(',', $value));
        $values = $this->filterEmptyValues($values);
        
        return !empty($values) ? array_map('intval', $values) : null;
    }

    /**
     * Remove empty and null values from array.
     *
     * @param array<string> $values
     * @return array<string>
     */
    private function filterEmptyValues(array $values): array
    {
        return array_filter($values, fn($value) => $value !== '' && $value !== null);
    }
}

