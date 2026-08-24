<?php

namespace App\Console\Commands;

use App\Services\ApiSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:import-locations')]
#[Description('Command description')]
class ImportLocations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle():void
    {
        $this->info('Начало импорта локаций...');

        $unsynced_locations = DB::table('locations')
            ->where('active', 1)
            ->where(function ($query) {
                $query->whereNull('synced_at')
                    ->orWhereColumn('synced_at', '<', 'updated_at');
            })->exists();

        DB::table('locations')
            ->where('active', 1)
            ->where(function ($query) {
                $query->whereNull('synced_at')
                    ->orWhereColumn('synced_at', '<', 'updated_at');
            })
            ->orderBy('id')
            ->select('id', 'parent', 'name', 'type', 'group', 'updated_at')
            ->chunk(500, function ($locations) {
                $this->processBatch($locations->toArray());
            });

        $this->info('Импорт категорий завершён.');

        if (!$unsynced_locations) {
            $this->call('app:import-products');
        }
    }

    protected function processBatch(array $locations): void
    {
        if (!empty($locations)) {
            $service = new ApiSyncService();
            $result = $service->importLocations($locations);

            if ($result['status']) {
                $now = now();
                foreach ($locations as $location) {
                    $affected = DB::table('locations')
                        ->where('id', $location->id)
                        ->where('updated_at', $location->updated_at)
                        ->update(['synced_at' => $now]);

                    if ($affected === 0) {
                        $this->warn("Пропущена локация ID {$location->id}: локация была обновлена.");
                    }
                }
            } else {
                $this->warn("Ошибка синхронизации локаций: {$result['message']}");
            }
        }
    }
}
