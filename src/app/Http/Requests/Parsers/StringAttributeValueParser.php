<?php

declare(strict_types=1);

namespace App\Http\Requests\Parsers;

class StringAttributeValueParser implements AttributeValueParserInterface
{
    /**
     * Parse STRING/BOOLEAN type attribute value.
     * Returns the value as-is.
     *
     * @param string $value String value
     * @return string
     */
    public function parse(string $value): string
    {
        return $value;
    }
}

