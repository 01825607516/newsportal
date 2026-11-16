<?php

namespace App\Console\Commands;

use App\Models\Portal;
use App\Services\TenantManager;
use App\Services\TenantProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantsMigrate extends Command
{
    protected $signature = 'tenants:migrate';
    protected $description = 'Apply new database/tenant/migrations/*.sql files and refresh the views in every tenant database';

    public function handle(TenantProvisioner $provisioner): int
    {
        $failed = 0;

        Portal::whereNotNull('database_name')->each(function (Portal $portal) use ($provisioner, &$failed) {
            try {
                if (!$provisioner->databaseExists($portal->database_name)) {
                    $this->warn("SKIP  {$portal->database_name} (database does not exist)");
                    return;
                }

                TenantManager::connect($portal);
                $tenant = DB::connection('tenant');

                $provisioner->runMigrations($tenant);
                $provisioner->createViews($tenant);

                $this->info("OK    {$portal->database_name}");
            } catch (Throwable $e) {
                $failed++;
                $this->error("FAIL  {$portal->database_name}: {$e->getMessage()}");
            }
        });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
