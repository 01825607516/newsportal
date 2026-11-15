<?php

namespace App\Services;

use App\Models\Portal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TenantProvisioner
{
    /**
     * Create the tenant database for a portal, import the schema and seed app_config.
     * Safe to call again: it only fills in what is missing.
     */
    public function provision(Portal $portal): void
    {
        $dbName = $portal->database_name ?: config('tenancy.database_prefix') . $portal->id;

        try {
            // dbName is built from a prefix + integer id, but we still restrict it to be safe.
            if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $dbName)) {
                throw new \RuntimeException("Invalid tenant database name: {$dbName}");
            }

            TenantManager::server()->statement(
                "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
            );

            $portal->database_name = $dbName;
            TenantManager::connect($portal);

            $tenant = DB::connection('tenant');

            if (empty($tenant->select("SHOW TABLES LIKE 'app_config'"))) {
                $this->importSchema($tenant);
            }

            $this->seedConfig($tenant, $portal);

            $portal->provisioned_at = now();
            $portal->provision_error = null;
            $portal->save();
        } catch (Throwable $e) {
            Log::error('Tenant provisioning failed for portal ' . $portal->id . ': ' . $e->getMessage());
            $portal->provision_error = $e->getMessage();
            $portal->save();
            throw $e;
        }
    }

    /** Drop the tenant database (used when admin deletes a portal). */
    public function destroy(Portal $portal): void
    {
        if ($portal->database_name && preg_match('/^[A-Za-z0-9_]{1,64}$/', $portal->database_name)) {
            TenantManager::server()->statement("DROP DATABASE IF EXISTS `{$portal->database_name}`");
        }
    }

    private function importSchema($connection): void
    {
        $sql = file_get_contents(config('tenancy.schema_file'));
        $withData = config('tenancy.import_sample_data');

        foreach ($this->splitStatements($sql) as $statement) {
            $head = strtoupper(substr(ltrim($statement), 0, 20));

            if (str_starts_with($head, 'START TRANSACTION') || str_starts_with($head, 'COMMIT')) {
                continue;
            }
            if (!$withData && str_starts_with($head, 'INSERT')) {
                continue;
            }

            $connection->unprepared($statement);
        }
    }

    private function seedConfig($connection, Portal $portal): void
    {
        if ($connection->table('app_config')->exists()) {
            return;
        }

        $connection->table('app_config')->insert([
            'id' => 1,
            'time_zone' => 'Asia/Dhaka',
            'portal_name' => mb_substr($portal->portal_name, 0, 50),
            'email' => $portal->editor_email,
            'contact' => $portal->editor_mobile,
            'last_modifier' => 0,
        ]);
    }

    /** Splits a phpMyAdmin-style dump into statements (aware of quoted strings and comments). */
    private function splitStatements(string $sql): array
    {
        $statements = [];
        $buf = '';
        $inStr = false;
        $quote = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $c = $sql[$i];

            if ($inStr) {
                $buf .= $c;
                if ($c === '\\' && $i + 1 < $len) {
                    $buf .= $sql[++$i];
                } elseif ($c === $quote) {
                    if ($i + 1 < $len && $sql[$i + 1] === $quote) {
                        $buf .= $sql[++$i];
                    } else {
                        $inStr = false;
                    }
                }
                continue;
            }

            // -- line comment
            if ($c === '-' && ($sql[$i + 1] ?? '') === '-' && in_array($sql[$i + 2] ?? "\n", [' ', "\n", "\r", "\t"], true)) {
                $nl = strpos($sql, "\n", $i);
                $i = $nl === false ? $len : $nl;
                continue;
            }

            // /* block comment */  (keep executable /*! ... */ ones)
            if ($c === '/' && ($sql[$i + 1] ?? '') === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $end = $end === false ? $len - 1 : $end + 1;
                if (($sql[$i + 2] ?? '') === '!') {
                    $buf .= substr($sql, $i, $end - $i + 1);
                }
                $i = $end;
                continue;
            }

            if ($c === "'" || $c === '"') {
                $inStr = true;
                $quote = $c;
                $buf .= $c;
                continue;
            }

            if ($c === ';') {
                if (trim($buf) !== '') {
                    $statements[] = trim($buf);
                }
                $buf = '';
                continue;
            }

            $buf .= $c;
        }

        if (trim($buf) !== '') {
            $statements[] = trim($buf);
        }

        return $statements;
    }
}
