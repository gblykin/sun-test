<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductFilterService
{
    /**
     * Filter products based on criteria.
     *
     * @param array $filters Filter criteria
     * @param int $limit Number of results per page
     * @param int $page Current page number
     * @return LengthAwarePaginator
     */
    public function filter(array $filters, int $limit = 10, int $page = 1): LengthAwarePaginator
    {
        $query = Product::query();

        // Filter by category
        if (isset($filters['category_id'])) {
            $query->where('category_id', '=', $filters['category_id']);
        }

        // Filter by manufacturer
        if (isset($filters['manufacturer_id'])) {
            $query->where('manufacturer_id', '=', $filters['manufacturer_id']);
        }

        // Filter by price range
        if (isset($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Filter by attributes
        if (isset($filters['attributes']) && is_array($filters['attributes'])) {
            $this->applyAttributeFilters($query, $filters['attributes']);
        }

        // Load relationships for response
        $query->with(['category', 'manufacturer', 'attributeValues.attribute', 'attributeValues.attributeOption']);

        return $query->paginate($limit, ['*'], 'page', $page)->withQueryString();
    }

    /**
     * Apply attribute filters to the query.
     *
     * @param Builder $query
     * @param array $attributes Array of attribute filters
     * @return void
     */
    private function applyAttributeFilters(Builder $query, array $attributes): void
    {
        // Load attributes to determine their types
        $attributeIds = array_map(function ($filter) {
            return (int) ($filter['attribute_id'] ?? 0);
        }, $attributes);
        $attributeIds = array_filter($attributeIds);

        if (empty($attributeIds)) {
            return;
        }

        $attributesMap = Attribute::whereIn('id', $attributeIds)
            ->get()
            ->keyBy('id');

        foreach ($attributes as $attributeFilter) {
            if (!isset($attributeFilter['attribute_id']) || !isset($attributeFilter['value'])) {
                continue;
            }

            $attributeId = (int) $attributeFilter['attribute_id'];
            $value = $attributeFilter['value'];
            $attribute = $attributesMap->get($attributeId);

            if ($attribute === null) {
                continue;
            }

            $attributeType = AttributeType::tryFrom($attribute->type_id);

            $query->whereHas('attributeValues', function (Builder $subQuery) use ($attributeId, $value, $attributeType) {
                $subQuery->where('attribute_id', '=', $attributeId);

                // For LIST type, filter by attribute_option_id
                if ($attributeType === AttributeType::LIST) {
                    // Value can be array of option IDs or single option ID
                    $optionIds = is_array($value) ? $value : [$value];
                    $subQuery->whereIn('attribute_option_id', array_map('intval', $optionIds));
                } elseif ($attributeType === AttributeType::DECIMAL || $attributeType === AttributeType::INTEGER) {
                    // For numeric types, filter by value_decimal
                    if (is_array($value) && count($value) === 2) {
                        // Range: [min, max]
                        $subQuery->whereBetween('value_decimal', [(float) $value[0], (float) $value[1]]);
                    } else {
                        // Exact value
                        $subQuery->where('value_decimal', '=', (float) $value);
                    }
                } else {
                    // For STRING and BOOLEAN, filter by value_text
                    $subQuery->where('value_text', '=', (string) $value);
                }
            });
        }
    }
}

