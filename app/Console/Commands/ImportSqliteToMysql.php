<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportSqliteToMysql extends Command
{
    protected $signature = 'data:import-sqlite-to-mysql
        {--sqlite-connection=sqlite_legacy : SQLite connection name}
        {--mysql-connection=mysql : MySQL connection name}
        {--chunk=200 : Insert chunk size}
        {--truncate : Truncate MySQL tables before import}
        {--skip=* : Tables to skip (repeatable)}
        {--only=* : Import only these tables (repeatable)}
        {--dry-run : Show what would be imported without writing}';

    protected $description = 'One-time import from legacy SQLite database into MySQL tables';

    public function handle(): int
    {
        $sqliteConn = (string) $this->option('sqlite-connection');
        $mysqlConn = (string) $this->option('mysql-connection');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $truncate = (bool) $this->option('truncate');

        $skip = collect((array) $this->option('skip'))->filter()->values()->all();
        $only = collect((array) $this->option('only'))->filter()->values()->all();

        $sqliteDbPath = config("database.connections.$sqliteConn.database");
        if (! $sqliteDbPath || ! is_string($sqliteDbPath) || ! file_exists($sqliteDbPath)) {
            $this->error("SQLite legacy database file not found for connection [$sqliteConn].");
            $this->line("Set SQLITE_LEGACY_DATABASE in your .env to an absolute path, e.g.:");
            $this->line('SQLITE_LEGACY_DATABASE=C:\\path\\to\\old.sqlite');
            return self::FAILURE;
        }

        if (! in_array(config('database.default'), ['mysql', 'mariadb'], true) && $mysqlConn === config('database.default')) {
            $this->warn("Your default DB connection is not MySQL. This command will still write into [$mysqlConn].");
        }

        // Get SQLite tables
        $sqliteTables = collect(DB::connection($sqliteConn)
            ->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
            ->map(fn ($r) => $r->name)
            ->filter()
            ->values();

        // Default skip
        $skip = array_values(array_unique(array_merge(['migrations'], $skip)));

        if (! empty($only)) {
            $sqliteTables = $sqliteTables->filter(fn (string $t) => in_array($t, $only, true))->values();
        } else {
            $sqliteTables = $sqliteTables->reject(fn (string $t) => in_array($t, $skip, true))->values();
        }

        if ($sqliteTables->isEmpty()) {
            $this->warn('No tables selected for import.');
            return self::SUCCESS;
        }

        // Keep only tables that exist in MySQL
        $tables = $sqliteTables->filter(function (string $t) use ($mysqlConn) {
            try {
                return Schema::connection($mysqlConn)->hasTable($t);
            } catch (\Throwable) {
                return false;
            }
        })->values();

        $missing = $sqliteTables->diff($tables);
        if ($missing->isNotEmpty()) {
            $this->warn('These tables exist in SQLite but not in MySQL schema and will be skipped:');
            $this->line($missing->implode(', '));
        }

        if ($tables->isEmpty()) {
            $this->error('No matching tables to import (SQLite tables did not exist in MySQL).');
            return self::FAILURE;
        }

        $this->info('Import plan:');
        $this->line("- SQLite: $sqliteDbPath (connection: $sqliteConn)");
        $this->line("- MySQL connection: $mysqlConn");
        $this->line('- Tables: '.$tables->implode(', '));
        $this->line("- Chunk size: $chunkSize");
        $this->line('- Truncate: '.($truncate ? 'yes' : 'no'));
        $this->line('- Dry-run: '.($dryRun ? 'yes' : 'no'));

        if ($dryRun) {
            foreach ($tables as $table) {
                $count = (int) DB::connection($sqliteConn)->table($table)->count();
                $this->line("[$table] rows in SQLite: $count");
            }
            return self::SUCCESS;
        }

        $mysql = DB::connection($mysqlConn);
        $mysql->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                $this->line("Importing [$table]...");

                if ($truncate) {
                    $mysql->table($table)->truncate();
                }

                $mysqlColumns = Schema::connection($mysqlConn)->getColumnListing($table);
                $mysqlColumnsSet = array_fill_keys($mysqlColumns, true);

                $sqliteColumnsRaw = collect(DB::connection($sqliteConn)->select("PRAGMA table_info('$table')"))
                    ->map(fn ($r) => $r->name)
                    ->filter()
                    ->values()
                    ->all();

                // Build a list of allowed "clean" column names that exist in MySQL.
                // Also protects from legacy SQLite columns with whitespace/newlines in their names.
                $allowedColumns = [];
                foreach ($sqliteColumnsRaw as $c) {
                    $clean = is_string($c) ? trim($c) : (string) $c;
                    if ($clean === '' || ! isset($mysqlColumnsSet[$clean])) {
                        continue;
                    }
                    $allowedColumns[$clean] = true;
                }
                $allowedColumns = array_keys($allowedColumns);
                $allowedColumnsSet = array_fill_keys($allowedColumns, true);

                // Chunk by primary key if present, else use offset pagination
                $pk = collect(DB::connection($sqliteConn)->select("PRAGMA table_info('$table')"))
                    ->firstWhere('pk', 1);
                $pkName = is_object($pk) ? ($pk->name ?? null) : null;
                $pkName = is_string($pkName) ? trim($pkName) : $pkName;

                $imported = 0;

                if ($pkName && in_array($pkName, $allowedColumns, true)) {
                    DB::connection($sqliteConn)->table($table)
                        ->orderBy($pkName)
                        ->chunk($chunkSize, function ($rows) use ($mysql, $table, $allowedColumnsSet, &$imported) {
                            $payload = [];
                            foreach ($rows as $row) {
                                $arr = (array) $row;
                                $cleanRow = [];
                                foreach ($arr as $k => $v) {
                                    $cleanKey = is_string($k) ? trim($k) : (string) $k;
                                    if (! isset($allowedColumnsSet[$cleanKey])) {
                                        continue;
                                    }
                                    // If SQLite has duplicate keys after trim, keep the first one.
                                    if (! array_key_exists($cleanKey, $cleanRow)) {
                                        $cleanRow[$cleanKey] = $v;
                                    }
                                }
                                if ($cleanRow) {
                                    $payload[] = $cleanRow;
                                }
                            }
                            if ($payload) {
                                $mysql->table($table)->insert($payload);
                                $imported += count($payload);
                            }
                        });
                } else {
                    $offset = 0;
                    while (true) {
                        $rows = DB::connection($sqliteConn)->table($table)
                            ->offset($offset)
                            ->limit($chunkSize)
                            ->get();

                        if ($rows->isEmpty()) {
                            break;
                        }

                        $payload = [];
                        foreach ($rows as $row) {
                            $arr = (array) $row;
                            $cleanRow = [];
                            foreach ($arr as $k => $v) {
                                $cleanKey = is_string($k) ? trim($k) : (string) $k;
                                if (! isset($allowedColumnsSet[$cleanKey])) {
                                    continue;
                                }
                                if (! array_key_exists($cleanKey, $cleanRow)) {
                                    $cleanRow[$cleanKey] = $v;
                                }
                            }
                            if ($cleanRow) {
                                $payload[] = $cleanRow;
                            }
                        }

                        if ($payload) {
                            $mysql->table($table)->insert($payload);
                            $imported += count($payload);
                        }

                        $offset += $chunkSize;
                    }
                }

                $this->line("[$table] imported rows: $imported");
            }
        } finally {
            $mysql->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Import completed.');
        return self::SUCCESS;
    }
}

