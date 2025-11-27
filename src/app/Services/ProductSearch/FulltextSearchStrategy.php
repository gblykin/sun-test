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
     * Uses hybrid approach:
     * - BOOLEAN MODE for filtering (supports prefix matching with *)
     * - NATURAL LANGUAGE MODE for relevance scoring (uses FULLTEXT index efficiently)
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

        $trimmedQuery = trim($query);

        // Find manufacturers matching the search query
        $manufacturerIds = $this->findManufacturerIds($trimmedQuery);

        // Build search query with priorities
        $manufacturerIdsString = $manufacturerIds->isNotEmpty() 
            ? $manufacturerIds->implode(',') 
            : '0';
        
        // Prepare queries:
        // - Boolean query with prefix matching for filtering (pri* matches Prime)
        // - Natural language query for relevance scoring (uses original words)
        $booleanQuery = $this->prepareFulltextQuery($trimmedQuery); // "pri*" for prefix matching
        $naturalQuery = $trimmedQuery; // "pri" for relevance scoring
        
        $products = DB::table('products')
            ->select([
                'products.*',
                // Relevance by title using NATURAL LANGUAGE MODE (weight 3 - highest priority)
                // This uses FULLTEXT index efficiently and returns proper relevance scores
                DB::raw('(COALESCE(MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE), 0) * 3) as title_relevance'),
                // Relevance by description using NATURAL LANGUAGE MODE (weight 2 - medium priority)
                DB::raw('(COALESCE(MATCH(description) AGAINST(? IN NATURAL LANGUAGE MODE), 0) * 2) as desc_relevance'),
                // Relevance by manufacturer (weight 1 - lowest priority)
                DB::raw("(CASE WHEN manufacturer_id IN ({$manufacturerIdsString}) THEN 1 ELSE 0 END) as manufacturer_relevance"),
            ])
            ->setBindings([$naturalQuery, $naturalQuery])
            ->where(function ($queryBuilder) use ($booleanQuery, $manufacturerIds) {
                // Filter by title using BOOLEAN MODE (supports prefix matching with *)
                // This uses FULLTEXT index and finds "Prime" when searching for "pri*"
                $queryBuilder->whereRaw('MATCH(title) AGAINST(? IN BOOLEAN MODE)', [$booleanQuery]);

                // OR by description using BOOLEAN MODE
                $queryBuilder->orWhereRaw('MATCH(description) AGAINST(? IN BOOLEAN MODE)', [$booleanQuery]);

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
     * Uses only FULLTEXT index with BOOLEAN MODE for prefix matching.
     *
     * @param string $query Search query
     * @return \Illuminate\Support\Collection Collection of manufacturer IDs
     */
    private function findManufacturerIds(string $query): \Illuminate\Support\Collection
    {
        $booleanQuery = $this->prepareFulltextQuery($query);
        
        return DB::table('manufacturers')
            ->whereRaw('MATCH(title) AGAINST(? IN BOOLEAN MODE)', [$booleanQuery])
            ->pluck('id');
    }

    /**
     * Prepare FULLTEXT search query with prefix matching.
     * Adds wildcard (*) to each word for prefix matching.
     *
     * @param string $query
     * @return string
     */
    private function prepareFulltextQuery(string $query): string
    {
        // Split query into words and add wildcard for prefix matching
        $words = preg_split('/\s+/', trim($query));
        $words = array_filter($words, fn($word) => !empty($word));
        
        // Add wildcard to each word for prefix matching: "eco" -> "eco*"
        return implode(' ', array_map(fn($word) => $word . '*', $words));
    }

}

