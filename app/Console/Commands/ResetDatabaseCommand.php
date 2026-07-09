<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use PDO;

class ResetDatabaseCommand extends Command
{
    protected string $signature = 'reset-database';
    protected string $description = 'Drop all tables except core tables (users, settings, password_resets)';

    public function __construct(
        private readonly PDO $db
    ) {}

    public function handle(array $args): void
    {
        $this->info("Fetching tables to drop...");

        $excludedTables = ['users', 'settings', 'password_resets'];
        
        $stmt = $this->db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $tablesToDrop = array_filter($tables, fn($table) => !in_array($table, $excludedTables));

        if (empty($tablesToDrop)) {
            $this->info("No tables to drop. Database is clean.");
            return;
        }

        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tablesToDrop as $table) {
            $this->info("Dropping table: {$table}");
            $this->db->exec("DROP TABLE IF EXISTS `{$table}`");
        }

        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->info("Database reset completed successfully. Excluded tables were preserved.");
    }
}
