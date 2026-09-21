<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportDatabaseSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export {--file=ecommerce_db.sql : The output filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all MySQL database tables and live data directly into the project .sql file';

    /**
     * Execute the console command.
     */
    public static function exportSqlFile($filename = 'ecommerce_db.sql')
    {
        try {
            $databaseName = DB::connection()->getDatabaseName();
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $databaseName;

            $sqlOutput = "-- ==========================================================\n";
            $sqlOutput .= "-- Cartzy Live Database Dump / Schema\n";
            $sqlOutput .= "-- Database: `{$databaseName}`\n";
            $sqlOutput .= "-- Auto-synced at: " . date('Y-m-d H:i:s') . "\n";
            $sqlOutput .= "-- ==========================================================\n\n";
            $sqlOutput .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sqlOutput .= "START TRANSACTION;\n";
            $sqlOutput .= "SET time_zone = \"+00:00\";\n\n";
            $sqlOutput .= "CREATE DATABASE IF NOT EXISTS `{$databaseName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
            $sqlOutput .= "USE `{$databaseName}`;\n\n";

            foreach ($tables as $tableObj) {
                $table = $tableObj->$tableKey ?? current((array)$tableObj);

                // 1. Drop & Create Table Structure
                $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
                $createTableSql = $createTableResult[0]->{'Create Table'} ?? '';

                $sqlOutput .= "-- --------------------------------------------------------\n";
                $sqlOutput .= "-- Table structure for table `{$table}`\n";
                $sqlOutput .= "-- --------------------------------------------------------\n";
                $sqlOutput .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sqlOutput .= $createTableSql . ";\n\n";

                // 2. Dump Table Rows / Data
                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    $sqlOutput .= "-- Dumping data for table `{$table}` (" . $rows->count() . " rows)\n";
                    $columns = array_keys((array)$rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';

                    $rowInserts = [];
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) {
                            if (is_null($val)) return 'NULL';
                            return "'" . addslashes((string)$val) . "'";
                        }, (array)$row);

                        $rowInserts[] = '(' . implode(', ', $values) . ')';
                    }

                    $sqlOutput .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n" . implode(",\n", $rowInserts) . ";\n\n";
                }
            }

            $sqlOutput .= "COMMIT;\n";

            // Write to project root and database/ folder
            File::put(base_path('ecommerce_db.sql'), $sqlOutput);
            File::put(database_path('ecommerce_db.sql'), $sqlOutput);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function handle()
    {
        $this->info('Starting database export to project SQL file...');

        $success = self::exportSqlFile();

        if ($success) {
            $this->info('✓ Successfully exported database to:');
            $this->line('  - ' . base_path('ecommerce_db.sql'));
            $this->line('  - ' . database_path('ecommerce_db.sql'));
        } else {
            $this->error('Failed to export database. Please check MySQL connection in .env.');
        }

        return $success ? 0 : 1;
    }
}
