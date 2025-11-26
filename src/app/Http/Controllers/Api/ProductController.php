<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Requests\ProductSearchRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductFilterService;
use App\Services\ProductSearchService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductSearchService $searchService,
        private readonly ProductFilterService $filterService
    ) {
    }

    /**
     * Search for products.
     *
     * @param ProductSearchRequest $request
     * @return JsonResponse
     */
    public function search(ProductSearchRequest $request): JsonResponse
    {
        $products = $this->searchService->search(
            $request->getQuery(),
            $request->getLimit()
        );

        return ProductResource::collection($products)->response();
    }

    /**
     * Filter products.
     *
     * @param ProductFilterRequest $request
     * @return JsonResponse
     */
    public function filter(ProductFilterRequest $request): JsonResponse
    {
        $products = $this->filterService->filter(
            $request->getFilters(),
            $request->getLimit(),
            $request->getPage()
        );

        return ProductResource::collection($products)->response();
    }
}

