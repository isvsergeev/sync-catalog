<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ImportController extends Controller
{
    public function importCategories(): void
    {
        Artisan::call('app:import-categories');
    }

    public function importLocations(): void
    {
        Artisan::call('app:import-locations');
    }

    public function importProducts(): void
    {
        Artisan::call('app:import-products');
    }
}
