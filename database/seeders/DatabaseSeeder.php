<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedLocations();
        $this->seedProducts();
    }

    private function seedCategories(): void
    {
        $categories = [];
        for ($i = 1; $i <= 10; $i++) {
            $categories[] = [
                'id'         => (string) Str::uuid(),
                'parent'     => (string) Str::uuid(),
                'active'     => rand(0, 1),
                'name'       => 'Category ' . $i,
                'url'        => 'https://example.com/category',
                'alias'      => 'alias/category-' . $i,
                'updated_at' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
                'synced_at'  => null,
            ];
        }
        DB::table('categories')->insert($categories);
    }

    private function seedLocations(): void
    {
        $locations = [];
        $types = ['city', 'store'];
        $groups = ['North', 'East', 'South', 'West'];
        for ($i = 1; $i <= 10; $i++) {
            $locations[] = [
                'id'         => (string) Str::uuid(),
                'parent'     => (string) Str::uuid(),
                'active'     => rand(0, 1),
                'name'       => 'Location ' . $i,
                'type'       => $types[array_rand($types)],
                'group'      => $groups[array_rand($groups)],
                'updated_at' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
                'synced_at'  => null,
            ];
        }
        DB::table('locations')->insert($locations);
    }

    private function seedProducts(): void
    {
        $products = [];
        for ($i = 1; $i <= 100; $i++) {
            $active = rand(0, 1);
            $products[] = [
                'id'         => (string) Str::uuid(),
                'active'     => $active,
                'json'       => json_encode([
                    'available'   => $active,
                    'name'        => 'Product ' . $i,
                    'picture'     => 'https://example.com/image.jpg',
                    'price'       => rand(100, 100000) / 100,
                    'url'         => 'https://example.com/product',
                    'description' => 'Description for product ' . $i,
                ]),
                'updated_at' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
                'synced_at'  => null,
            ];
        }
        DB::table('products')->insert($products);
    }
}
