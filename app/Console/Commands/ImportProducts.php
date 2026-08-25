<?php

namespace App\Console\Commands;

use App\Services\ApiSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('app:import-products')]
#[Description('Command description')]
class ImportProducts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Начало импорта оферов...');

        // Отключение товаров не в наличии
        DB::table('products')
            ->where('active', 0)
            ->where(function ($query) {
                $query->whereNull('synced_at')
                    ->orWhereColumn('synced_at', '<', 'updated_at');
            })
            ->orderBy('id')
            ->select('id', 'updated_at')
            ->chunk(500, function ($products) {
                $this->processDeleteBatch($products->toArray());
            });

        // Обновление товаров в наличии
        DB::table('products')
            ->where('active', 1)
            ->where(function ($query) {
                $query->whereNull('synced_at')
                    ->orWhereColumn('synced_at', '<', 'updated_at');
            })
            ->orderBy('id')
            ->select('id', 'json', 'updated_at')
            ->chunk(500, function ($products) {
                $this->processBatch($products->toArray());
            });

        $this->info('Импорт оферов завершён.');
    }

    protected function processDeleteBatch(array $product_ids): void
    {
        if (!empty($product_ids)) {
            $service = new ApiSyncService();
            $result = $service->deleteProducts($product_ids);

            if ($result['status']) {
                $now = now();
                foreach ($product_ids as $product) {
                    $affected = DB::table('products')
                        ->where('id', $product->id)
                        ->where('updated_at', $product->updated_at)
                        ->update(['synced_at' => $now]);

                    if ($affected === 0) {
                        $this->warn("Пропущен офер ID {$product->id}: офер был обновлен.");
                    }
                }
            } else {
                $this->warn("Ошибка синхронизации оферов: {$result['message']}");
            }
        }
    }

    protected function processBatch(array $products): void
    {
        if (!empty($products)) {
            $products_data = array_column($products, 'json');
            $decoded = array_map(function($json) {
                $json = preg_replace('/,\s*]/', ']', $json);
                $json = preg_replace('/,\s*}/', '}', $json);

                $result = json_decode($json, true);

                if ($result === null) {
                    Log::error('JSON decode error: ' . json_last_error_msg());
                }

                return $result;
            }, $products_data);
            $service = new ApiSyncService();
            $result = $service->importProducts($decoded);

            if ($result['status']) {
                $now = now();
                foreach ($products as $product) {
                    $affected = DB::table('products')
                        ->where('id', $product->id)
                        ->where('updated_at', $product->updated_at)
                        ->update(['synced_at' => $now]);

                    if ($affected === 0) {
                        $this->warn("Пропущен офер ID {$product->id}: офер был обновлен.");
                    }
                }
            } else {
                $this->warn("Ошибка синхронизации оферов: {$result['message']}");
            }
        }
    }
}
