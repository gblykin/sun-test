<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ManufacturerController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products', [ProductController::class, 'filter']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}/attributes', [CategoryController::class, 'attributes']);

Route::get('/manufacturers', [ManufacturerController::class, 'index']);

