<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ManufacturerResource;
use App\Models\Manufacturer;
use Illuminate\Http\JsonResponse;

class ManufacturerController extends Controller
{
    /**
     * Get all manufacturers.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $manufacturers = Manufacturer::orderBy('title')->get();

        return ManufacturerResource::collection($manufacturers)->response();
    }
}


