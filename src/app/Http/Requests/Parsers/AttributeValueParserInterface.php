<?php

declare(strict_types=1);

namespace App\Http\Requests\Parsers;

interface AttributeValueParserInterface
{
    /**
     * Parse attribute value from URL format.
     *
     * @param string $value Value from URL (e.g., "400:600", "1,2,3", ":600", "400:")
     * @return array|string|int|float|null Parsed value
     */
    public function parse(string $value): array|string|int|float|null;
}

