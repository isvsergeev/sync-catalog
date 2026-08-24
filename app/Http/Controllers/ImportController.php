<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ImportController extends Controller
{
    public function importCategories(): void
    {
        Artisan::call('import:categories');
    }

    public function importLocations(): void
    {
        Artisan::call('import:locations');
    }

    public function importProducts(): void
    {
        Artisan::call('import:products');
    }
}
