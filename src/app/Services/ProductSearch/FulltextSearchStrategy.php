<?php

declare(strict_types=1);

namespace App\Services\ProductSearch;

use App\Contracts\ProductSearchStrategyInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FulltextSearchStrategy implements ProductSearchStrategyInterface
{
    /**
     * Search for products using FULLTEXT indexes with priorities.
     * Priority order: title (3) > description (2) > manufacturer (1)
     *
     * @param string $query Search query
     * @param int $limit Maximum number of results
     * @return Collection Collection of products
     */
    public function search(string $query, int $limit = 10): Collection
    {
        if (empty(trim($query))) {
            return Collection::make();
        }

        // Find manufacturers matching the search query
        $manufacturerIds = $this->findManufacturerIds($query);

        // Build search query with priorities
        $manufacturerIdsString = $manufacturerIds->isNotEmpty() 
            ? $manufacturerIds->implode(',') 
            : '0';
        
        $products = DB::table('products')
            ->select([
                'products.*',
                // Relevance by title (weight 3 - highest priority)
                DB::raw('(MATCH(title) AGAINST(? IN BOOLEAN MODE) * 3) as title_relevance'),
                // Relevance by description (weight 2 - medium priority)
                DB::raw('(MATCH(description) AGAINST(? IN BOOLEAN MODE) * 2) as desc_relevance'),
                // Relevance by manufacturer (weight 1 - lowest priority)
                DB::raw("(CASE WHEN manufacturer_id IN ({$manufacturerIdsString}) THEN 1 ELSE 0 END) as manufacturer_relevance")
            ])
            ->setBindings([$query, $query])
            ->where(function ($queryBuilder) use ($query, $manufacturerIds) {
                // Search by title
                $queryBuilder->whereRaw('MATCH(title) AGAINST(? IN BOOLEAN MODE)', [$query]);

                // OR by description
                $queryBuilder->orWhereRaw('MATCH(description) AGAINST(? IN BOOLEAN MODE)', [$query]);

                // OR by manufacturer
                if ($manufacturerIds->isNotEmpty()) {
                    $queryBuilder->orWhereIn('manufacturer_id', $manufacturerIds);
                }
            })
            ->orderByRaw('(title_relevance + desc_relevance + manufacturer_relevance) DESC')
            ->limit($limit)
            ->get();

        // Convert to Eloquent models
        return Product::hydrate($products->toArray());
    }

    /**
     * Find manufacturer IDs matching the search query.
     *
     * @param string $query Search query
     * @return \Illuminate\Support\Collection Collection of manufacturer IDs
     */
    private function findManufacturerIds(string $query): \Illuminate\Support\Collection
    {
        return DB::table('manufacturers')
            ->whereRaw('MATCH(title) AGAINST(? IN BOOLEAN MODE)', [$query])
            ->pluck('id');
    }
}

