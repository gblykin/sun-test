<?php

declare(strict_types=1);

namespace App\Http\Requests\Parsers;

use App\Enums\AttributeType;

class AttributeValueParserFactory
{
    /**
     * Create appropriate parser based on attribute type.
     *
     * @param AttributeType $attributeType
     * @return AttributeValueParserInterface
     */
    public function create(AttributeType $attributeType): AttributeValueParserInterface
    {
        return match ($attributeType) {
            AttributeType::LIST => new ListAttributeValueParser(),
            AttributeType::DECIMAL, AttributeType::INTEGER => new NumericAttributeValueParser(),
            AttributeType::BOOLEAN => new BooleanAttributeValueParser(),
            AttributeType::STRING => new StringAttributeValueParser(),
        };
    }
}

