<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ProductSearchStrategyInterface
{
    /**
     * Search for products by query string.
     *
     * @param string $query Search query
     * @param int $limit Maximum number of results
     * @return Collection Collection of products
     */
    public function search(string $query, int $limit = 10): Collection;
}

