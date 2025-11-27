<?php

declare(strict_types=1);

namespace App\Services\ProductFilter;

use App\Enums\AttributeType;

class AttributeFilterStrategyFactory
{
    /**
     * Create appropriate filter strategy based on attribute type.
     *
     * @param AttributeType $attributeType
     * @return AttributeFilterStrategyInterface
     * @throws \InvalidArgumentException If attribute type is not supported
     */
    public function create(AttributeType $attributeType): AttributeFilterStrategyInterface
    {
        return match ($attributeType) {
            AttributeType::LIST => new ListAttributeFilterStrategy(),
            AttributeType::DECIMAL, AttributeType::INTEGER => new NumericAttributeFilterStrategy(),
            AttributeType::BOOLEAN => new BooleanAttributeFilterStrategy(),
            AttributeType::STRING => new StringAttributeFilterStrategy(),
        };
    }
}

