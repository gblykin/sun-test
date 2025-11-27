<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttributeResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories or filter by slug.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($request->has('slug')) {
            $query->where('slug', '=', $request->input('slug'));
        }

        $categories = $query->get();

        return CategoryResource::collection($categories)->response();
    }

    /**
     * Get category attributes.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function attributes(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $attributes = $category->attributes()->with('options')->get();

        return AttributeResource::collection($attributes)->response();
    }
}

