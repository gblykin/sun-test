<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products', [ProductController::class, 'filter']);

