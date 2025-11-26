<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductSearchStrategyInterface;
use App\Services\ProductSearch\FulltextSearchStrategy;
use Illuminate\Database\Eloquent\Collection;

class ProductSearchService
{
    private ProductSearchStrategyInterface $strategy;

    public function __construct(?ProductSearchStrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new FulltextSearchStrategy();
    }

    /**
     * Set search strategy.
     *
     * @param ProductSearchStrategyInterface $strategy
     * @return self
     */
    public function setStrategy(ProductSearchStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Search for products.
     *
     * @param string $query Search query
     * @param int $limit Maximum number of results
     * @return Collection Collection of products
     */
    public function search(string $query, int $limit = 10): Collection
    {
        return $this->strategy->search($query, $limit);
    }
}

