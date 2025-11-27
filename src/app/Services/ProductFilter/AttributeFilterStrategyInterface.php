<?php

declare(strict_types=1);

namespace App\Services\ProductFilter;

use Illuminate\Database\Eloquent\Builder;

interface AttributeFilterStrategyInterface
{
    /**
     * Apply filter to the query builder.
     *
     * @param Builder $query Query builder for attribute_values table
     * @param int $attributeId Attribute ID
     * @param mixed $value Filter value
     * @return void
     */
    public function apply(Builder $query, int $attributeId, mixed $value): void;
}

