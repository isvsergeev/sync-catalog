<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::post('/categories', [ImportController::class, 'importCategories']);
Route::post('/locations', [ImportController::class, 'importLocations']);
Route::post('/products', [ImportController::class, 'importProducts']);
