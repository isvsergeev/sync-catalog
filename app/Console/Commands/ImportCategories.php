<?php

namespace App\Console\Commands;

use App\Services\ApiSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:import-categories')]
#[Description('Импорт категорий')]
class ImportCategories extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Начало импорта категорий...');

        $unsynced_categories = DB::table('categories')
            ->where('active', 1)
            ->where(function ($query) {
                $query->whereNull('synced_at')
                    ->orWhereColumn('synced_at', '<', 'updated_at');
            })->exists();

        DB::table('categories')
            ->where('active', 1)
            ->where(function ($query) {
                $query->whereNull('synced_at')
                    ->orWhereColumn('synced_at', '<', 'updated_at');
            })
            ->orderBy('id')
            ->select('id', 'parent', 'name', 'url', 'alias', 'updated_at')
            ->chunkById(500, function ($categories) {
                $this->processBatch($categories->toArray());
            }, 'id');

        $this->info('Импорт категорий завершён.');

        if (!$unsynced_categories) {
            $this->call('app:import-locations');
        }
    }

    protected function processBatch(array $categories): void
    {
        $service = new ApiSyncService();
        $result = $service->importCategories($categories);

        if ($result['status']) {
            $now = now();
            foreach ($categories as $category) {
                $affected = DB::table('categories')
                    ->where('id', $category->id)
                    ->where('updated_at', $category->updated_at)
                    ->update(['synced_at' => $now]);

                if ($affected === 0) {
                    $this->warn("Пропущена категория ID {$category->id}: категория была обновлена.");
                }
            }
        } else {
            $this->warn("Ошибка синхронизации категорий: {$result['message']}");
        }
    }
}
