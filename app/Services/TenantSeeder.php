<?php

namespace App\Services;

use App\Models\Portal;

/**
 * Fills a brand-new tenant database with its first rows.
 * To reuse this code for a different system, write your own class with a seed() method
 * and set it in config/tenancy.php ('seeder').
 */
class TenantSeeder
{
    public function seed($connection, Portal $portal): void
    {
        if ($connection->table('app_config')->exists()) {
            return;
        }

        $connection->table('app_config')->insert([
            'id' => 1,
            'time_zone' => config('tenancy.default_time_zone', 'Asia/Dhaka'),
            'portal_name' => mb_substr($portal->portal_name, 0, 50),
            'email' => $portal->editor_email,
            'contact' => $portal->editor_mobile,
            'last_modifier' => 0,
        ]);
    }
}
