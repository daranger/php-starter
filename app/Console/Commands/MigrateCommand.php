<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use PDO;

class MigrateCommand extends Command
{
    protected string $signature = 'migrate';
    protected string $description = 'Run the database migrations';

    public function __construct(
        private readonly PDO $db
    ) {}

    public function handle(array $args): void
    {
        $this->info("Starting migrations...");

        $this->createMigrationsTableIfNotExists();

        $migrationsPath = __DIR__ . '/../../../database/Migrations';
        if (!is_dir($migrationsPath)) {
            $this->warning("Migrations directory not found at {$migrationsPath}");
            return;
        }

        $files = scandir($migrationsPath);
        $migrationsToRun = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (!$this->hasMigrationRun($file)) {
                $migrationsToRun[] = $file;
            }
        }

        if (empty($migrationsToRun)) {
            $this->info("Nothing to migrate.");
            return;
        }

        foreach ($migrationsToRun as $file) {
            $this->info("Migrating: {$file}");
            
            $sql = file_get_contents($migrationsPath . '/' . $file);
            $this->db->exec($sql);
            
            $this->logMigration($file);
            
            $this->info("Migrated: {$file}");
        }

        $this->info("Migrations completed successfully.");
    }

    private function createMigrationsTableIfNotExists(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    private function hasMigrationRun(string $migration): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM migrations WHERE migration = :migration");
        $stmt->execute(['migration' => $migration]);
        return (bool) $stmt->fetch();
    }

    private function logMigration(string $migration): void
    {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
        $stmt->execute(['migration' => $migration]);
    }
}
