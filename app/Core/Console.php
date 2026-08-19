<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CLI command runner (bin/bitezy).
 */
final class Console
{
    public function __construct(private readonly Application $app)
    {
    }

    public function run(string $command, array $args = []): void
    {
        match ($command) {
            'help' => $this->help(),
            'key:generate' => $this->generateKey(),
            'db:migrate' => $this->migrate(),
            'db:seed' => $this->seed(),
            'db:refresh' => $this->refresh(),
            'cache:clear' => $this->clearCache(),
            default => $this->error("Unknown command \"$command\". Run \"php bin/bitezy help\" for usage."),
        };
    }

    private function help(): void
    {
        $this->line('Bitezy CLI');
        $this->line('  php bin/bitezy key:generate      Generate an APP_KEY');
        $this->line('  php bin/bitezy db:migrate        Apply pending database/migrations');
        $this->line('  php bin/bitezy db:seed           Run database/seeders/seed.php');
        $this->line('  php bin/bitezy db:refresh        Reset schema + seed');
        $this->line('  php bin/bitezy cache:clear       Clear cached storage');
        $this->line('  php bin/bitezy help              Show this help');
    }

    private function generateKey(): void
    {
        $key = 'base64:' . base64_encode(random_bytes(32));
        $envFile = $this->app->path('.env');
        $this->line("Generated APP_KEY: {$key}");
        if (is_file($envFile)) {
            $content = (string)file_get_contents($envFile);
            $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $content) ?? $content;
            file_put_contents($envFile, $content);
            $this->line('Wrote APP_KEY to .env');
        }
    }

    private function migrate(): void
    {
        $db = Database::connect();
        $this->ensureMigrationTable($db);
        $applied = $this->appliedMigrations($db);
        $files = glob($this->app->path('database/migrations/*.php')) ?: [];
        sort($files);

        $ran = 0;
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (isset($applied[$name])) {
                continue;
            }
            $migration = require $file;
            foreach ((array)($migration['up'] ?? []) as $statement) {
                try {
                    $db->execute($statement);
                } catch (\PDOException $e) {
                    $code = (int)($e->errorInfo[1] ?? 0);
                    if (in_array($code, [1050, 1060, 1061, 1062, 1091, 1826], true)) {
                        continue;
                    }
                    throw $e;
                }
            }
            $db->execute('INSERT INTO migrations (migration, batch) VALUES (?, 1)', [$name]);
            $applied[$name] = true;
            $ran++;
        }

        if ($ran === 0) {
            $this->line('Nothing to migrate.');
            return;
        }
        $this->line("Migrated: {$ran} pending migration(s).");
    }

    private function ensureMigrationTable(Database $db): void
    {
        $db->execute(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL DEFAULT 1,
                ran_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * @return array<string, true>
     */
    private function appliedMigrations(Database $db): array
    {
        $applied = [];
        foreach ($db->query('SELECT migration FROM migrations') ?: [] as $row) {
            $applied[(string)$row['migration']] = true;
        }
        return $applied;
    }

    private function seed(): void
    {
        $file = $this->app->path('database/seeders/seed.php');
        if (!is_file($file)) {
            $this->error('Seeder not found: ' . $file);
            return;
        }
        require $file;
    }

    private function refresh(): void
    {
        $name = config('database.name', 'bitezy');
        $db = Database::connect();
        $db->execute('DROP DATABASE IF EXISTS `' . $name . '`');
        $db->execute('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        Database::disconnect();
        $this->migrate();
        $this->seed();
    }

    private function clearCache(): void
    {
        $dir = $this->app->path('storage/cache');
        foreach (glob($dir . '/*') ?: [] as $file) {
            @unlink($file);
        }
        $this->line('Cache cleared.');
    }

    private function line(string $text): void
    {
        fwrite(STDOUT, $text . PHP_EOL);
    }

    private function error(string $text): void
    {
        fwrite(STDERR, $text . PHP_EOL);
    }
}
