<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\Product;
use App\Services\ProductFilter\AttributeFilterStrategyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductFilterService
{
    public function __construct(
        private readonly AttributeFilterStrategyFactory $strategyFactory
    ) {
    }
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

        // Filter by manufacturer(s) - can be single ID or array of IDs
        if (isset($filters['manufacturer_id'])) {
            if (is_array($filters['manufacturer_id'])) {
                $query->whereIn('manufacturer_id', $filters['manufacturer_id']);
            } else {
                $query->where('manufacturer_id', '=', $filters['manufacturer_id']);
            }
        }

        // Filter by price range
        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }
        
        // Validate price range if both are set
        if (!empty($filters['price_min']) && !empty($filters['price_max'])
            && $filters['price_min'] > $filters['price_max']) {
            // If min > max, return empty results
            $query->whereRaw('1 = 0');
        }

        // Filter by attributes
        if (isset($filters['attributes']) && is_array($filters['attributes'])) {
            $this->applyAttributeFilters($query, $filters['attributes']);
        }

        // Load relationships for response
        // Load category attributes to filter product attributes
        $query->with([
            'category',
            'category.attributes', // Load category attributes to filter product attributes
            'manufacturer',
            'attributeValues.attribute',
            'attributeValues.attributeOption'
        ]);

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

            if ($attributeType === null) {
                continue;
            }

            $strategy = $this->strategyFactory->create($attributeType);

            $query->whereHas('attributeValues', function (Builder $subQuery) use ($attributeId, $value, $strategy) {
                $strategy->apply($subQuery, $attributeId, $value);
            });
        }
    }
}

