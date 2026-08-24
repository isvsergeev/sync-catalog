<?php

namespace App\Console\Commands;

use App\Services\ApiSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

    protected function processBatch(array $products): void
    {
        if (!empty($products)) {
            $service = new ApiSyncService();
            $result = $service->importProducts($products);

            if ($result['status']) {
                $now = now();
                foreach ($products as $product) {
                    $affected = DB::table('locations')
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
