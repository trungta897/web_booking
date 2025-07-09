<?php

namespace App\Console\Commands;

use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseRestore extends Command
{
    protected $signature = 'db:restore
                            {file? : Path to backup file}
                            {--list : List available backup files}
                            {--latest : Restore from latest backup}
                            {--force : Skip confirmation prompts}
                            {--decompress : Decompress backup before restore}';

    protected $description = 'Restore database from backup file';

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('🔄 Database Restore Tool');
        $this->newLine();

        try {
            // Handle list option
            if ($this->option('list')) {
                return $this->listBackups();
            }

            // Get backup file
            $backupFile = $this->getBackupFile();

            if (!$backupFile) {
                $this->error('❌ No backup file specified or found');
                return Command::FAILURE;
            }

            // Validate backup file
            $this->validateBackupFile($backupFile);

            // Show restore information
            $this->displayRestoreInfo($backupFile);

            // Confirm restore operation
            if (!$this->confirmRestore()) {
                $this->info('🚫 Restore operation cancelled');
                return Command::SUCCESS;
            }

            // Prepare for restore
            $tempFile = $this->prepareBackupFile($backupFile);

            // Perform restore
            $this->performRestore($tempFile);

            // Cleanup temporary files
            if ($tempFile !== $backupFile) {
                unlink($tempFile);
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->info("✅ Database restored successfully in {$duration}s");
            $this->newLine();
            $this->warn('⚠️  Please verify your application functionality');

            // Log successful restore
            LogService::database('Database restored successfully', [
                'backup_file' => basename($backupFile),
                'duration_seconds' => $duration,
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Restore failed: " . $e->getMessage());

            LogService::error('Database restore failed', $e, [
                'backup_file' => isset($backupFile) ? basename($backupFile) : 'unknown',
                'duration' => round(microtime(true) - $startTime, 2),
            ]);

            return Command::FAILURE;
        }
    }

    private function listBackups(): int
    {
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            $this->warn('📁 No backup directory found');
            return Command::SUCCESS;
        }

        $backups = glob($backupDir . '/*.sql*');

        if (empty($backups)) {
            $this->info('📁 No backup files found');
            return Command::SUCCESS;
        }

        // Sort by modification time (newest first)
        usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));

        $tableData = [];
        foreach ($backups as $backup) {
            $filename = basename($backup);
            $size = $this->formatBytes(filesize($backup));
            $date = Carbon::createFromTimestamp(filemtime($backup))->format('Y-m-d H:i:s');
            $age = Carbon::createFromTimestamp(filemtime($backup))->diffForHumans();

            $tableData[] = [$filename, $size, $date, $age];
        }

        $this->table(
            ['Filename', 'Size', 'Created', 'Age'],
            $tableData
        );

        $this->newLine();
        $this->info('💡 To restore a backup, run: php artisan db:restore <filename>');

        return Command::SUCCESS;
    }

    private function getBackupFile(): ?string
    {
        $file = $this->argument('file');

        if ($file) {
            // Check if it's a full path or just filename
            if (file_exists($file)) {
                return $file;
            }

            // Try in backup directory
            $backupPath = storage_path('app/backups/' . $file);
            if (file_exists($backupPath)) {
                return $backupPath;
            }

            throw new \Exception("Backup file not found: {$file}");
        }

        if ($this->option('latest')) {
            return $this->getLatestBackup();
        }

        // Interactive selection
        return $this->selectBackupInteractively();
    }

    private function getLatestBackup(): ?string
    {
        $backupDir = storage_path('app/backups');
        $backups = glob($backupDir . '/*.sql*');

        if (empty($backups)) {
            return null;
        }

        // Get the most recent backup
        usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));

        return $backups[0];
    }

    private function selectBackupInteractively(): ?string
    {
        $backupDir = storage_path('app/backups');
        $backups = glob($backupDir . '/*.sql*');

        if (empty($backups)) {
            $this->warn('📁 No backup files found');
            return null;
        }

        // Sort by modification time (newest first)
        usort($backups, fn($a, $b) => filemtime($b) - filemtime($a));

        $choices = [];
        foreach ($backups as $index => $backup) {
            $filename = basename($backup);
            $age = Carbon::createFromTimestamp(filemtime($backup))->diffForHumans();
            $size = $this->formatBytes(filesize($backup));

            $choices[] = "{$filename} ({$size}, {$age})";
        }

        $selected = $this->choice('Select a backup file to restore', $choices);
        $selectedIndex = array_search($selected, $choices);

        return $backups[$selectedIndex];
    }

    private function validateBackupFile(string $backupFile): void
    {
        if (!file_exists($backupFile)) {
            throw new \Exception("Backup file does not exist: {$backupFile}");
        }

        if (!is_readable($backupFile)) {
            throw new \Exception("Backup file is not readable: {$backupFile}");
        }

        // Check if file is empty
        if (filesize($backupFile) === 0) {
            throw new \Exception("Backup file is empty: {$backupFile}");
        }
    }

    private function displayRestoreInfo(string $backupFile): void
    {
        $filename = basename($backupFile);
        $size = $this->formatBytes(filesize($backupFile));
        $date = Carbon::createFromTimestamp(filemtime($backupFile))->format('Y-m-d H:i:s');
        $dbName = config('database.connections.' . config('database.default') . '.database');

        $this->table(['Property', 'Value'], [
            ['Backup File', $filename],
            ['Size', $size],
            ['Created', $date],
            ['Target Database', $dbName],
        ]);
    }

    private function confirmRestore(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $this->newLine();
        $this->warn('⚠️  WARNING: This will replace all existing data in your database!');
        $this->warn('⚠️  Make sure you have a current backup before proceeding.');
        $this->newLine();

        return $this->confirm('Are you sure you want to continue with the restore?');
    }

    private function prepareBackupFile(string $backupFile): string
    {
        // Check if file is compressed
        if (str_ends_with($backupFile, '.gz')) {
            $this->info('🗜️  Decompressing backup file...');

            $tempFile = storage_path('app/temp_restore_' . time() . '.sql');
            $command = sprintf('gunzip -c %s > %s', escapeshellarg($backupFile), escapeshellarg($tempFile));

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Failed to decompress backup file');
            }

            $this->info('✅ Backup decompressed successfully');
            return $tempFile;
        }

        return $backupFile;
    }

    private function performRestore(string $backupFile): void
    {
        $this->info('🔄 Restoring database...');

        $dbConfig = config('database.connections.' . config('database.default'));

        // Build mysql command
        $command = $this->buildMysqlCommand($dbConfig, $backupFile);

        // Execute restore
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Restore failed: ' . implode("\n", $output));
        }

        $this->info('✅ Database restore completed');
    }

    private function buildMysqlCommand(array $dbConfig, string $backupFile): string
    {
        $host = $dbConfig['host'] ?? 'localhost';
        $port = $dbConfig['port'] ?? 3306;
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'] ?? '';

        $baseCommand = sprintf(
            'mysql --host=%s --port=%d --user=%s',
            escapeshellarg($host),
            $port,
            escapeshellarg($username)
        );

        if (!empty($password)) {
            $baseCommand .= ' --password=' . escapeshellarg($password);
        }

        return sprintf(
            '%s %s < %s',
            $baseCommand,
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
