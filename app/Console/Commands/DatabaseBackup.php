<?php

namespace App\Console\Commands;

use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup
                            {--type=full : Type of backup (full, structure, data)}
                            {--compress : Compress the backup file}
                            {--upload : Upload to cloud storage}
                            {--cleanup : Remove old backups}
                            {--name= : Custom backup name}';

    protected $description = 'Create a database backup with various options';

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('🗄️  Starting Database Backup...');
        $this->newLine();

        try {
            // Get configuration
            $dbConfig = config('database.connections.' . config('database.default'));
            $backupType = $this->option('type');
            $compress = $this->option('compress');
            $upload = $this->option('upload');
            $cleanup = $this->option('cleanup');
            $customName = $this->option('name');

            // Validate database connection
            $this->validateDatabaseConnection();

            // Generate backup filename
            $filename = $this->generateBackupFilename($backupType, $customName, $compress);
            $backupPath = storage_path('app/backups/' . $filename);

            // Ensure backup directory exists
            $this->ensureBackupDirectoryExists();

            // Create backup based on type
            $success = match ($backupType) {
                'full' => $this->createFullBackup($backupPath, $dbConfig),
                'structure' => $this->createStructureBackup($backupPath, $dbConfig),
                'data' => $this->createDataBackup($backupPath, $dbConfig),
                default => throw new \InvalidArgumentException("Invalid backup type: {$backupType}")
            };

            if (!$success) {
                throw new \Exception('Backup creation failed');
            }

            // Compress if requested
            if ($compress) {
                $compressedPath = $this->compressBackup($backupPath);
                $backupPath = $compressedPath;
                $filename = basename($compressedPath);
            }

            // Get backup file info
            $fileSize = $this->formatBytes(filesize($backupPath));
            $duration = round(microtime(true) - $startTime, 2);

            $this->info('✅ Backup created successfully!');
            $this->table(['Property', 'Value'], [
                ['Type', ucfirst($backupType)],
                ['Filename', $filename],
                ['Size', $fileSize],
                ['Duration', "{$duration}s"],
                ['Location', $backupPath],
            ]);

            // Upload to cloud if requested
            if ($upload) {
                $this->uploadToCloud($backupPath, $filename);
            }

            // Cleanup old backups if requested
            if ($cleanup) {
                $this->cleanupOldBackups();
            }

            // Log successful backup
            LogService::database('Database backup created successfully', [
                'type' => $backupType,
                'filename' => $filename,
                'size_bytes' => filesize($backupPath),
                'duration_seconds' => $duration,
                'compressed' => $compress,
                'uploaded' => $upload,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());

            LogService::error('Database backup failed', $e, [
                'backup_type' => $backupType ?? 'unknown',
                'duration' => round(microtime(true) - $startTime, 2),
            ]);

            return Command::FAILURE;
        }
    }

    private function validateDatabaseConnection(): void
    {
        $this->info('🔍 Validating database connection...');

        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection successful');
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    private function generateBackupFilename(string $type, ?string $customName, bool $compress): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $dbName = config('database.connections.' . config('database.default') . '.database');

        if ($customName) {
            $filename = "{$customName}_{$timestamp}";
        } else {
            $filename = "{$dbName}_{$type}_backup_{$timestamp}";
        }

        $extension = $compress ? '.sql.gz' : '.sql';

        return $filename . $extension;
    }

    private function ensureBackupDirectoryExists(): void
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            $this->info('📁 Created backup directory: ' . $backupDir);
        }
    }

    private function createFullBackup(string $backupPath, array $dbConfig): bool
    {
        $this->info('📦 Creating full backup (structure + data)...');

        $command = $this->buildMysqldumpCommand($dbConfig, $backupPath, [
            '--single-transaction',
            '--routines',
            '--triggers',
            '--complete-insert',
        ]);

        return $this->executeCommand($command);
    }

    private function createStructureBackup(string $backupPath, array $dbConfig): bool
    {
        $this->info('🏗️  Creating structure-only backup...');

        $command = $this->buildMysqldumpCommand($dbConfig, $backupPath, [
            '--no-data',
            '--routines',
            '--triggers',
        ]);

        return $this->executeCommand($command);
    }

    private function createDataBackup(string $backupPath, array $dbConfig): bool
    {
        $this->info('📊 Creating data-only backup...');

        $command = $this->buildMysqldumpCommand($dbConfig, $backupPath, [
            '--no-create-info',
            '--complete-insert',
            '--single-transaction',
        ]);

        return $this->executeCommand($command);
    }

    private function buildMysqldumpCommand(array $dbConfig, string $backupPath, array $options = []): string
    {
        $host = $dbConfig['host'] ?? 'localhost';
        $port = $dbConfig['port'] ?? 3306;
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'] ?? '';

        $baseCommand = sprintf(
            'mysqldump --host=%s --port=%d --user=%s',
            escapeshellarg($host),
            $port,
            escapeshellarg($username)
        );

        if (!empty($password)) {
            $baseCommand .= ' --password=' . escapeshellarg($password);
        }

        $optionsString = implode(' ', $options);

        return sprintf(
            '%s %s %s > %s',
            $baseCommand,
            $optionsString,
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );
    }

    private function executeCommand(string $command): bool
    {
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Command failed: ' . implode("\n", $output));

            return false;
        }

        return true;
    }

    private function compressBackup(string $backupPath): string
    {
        $this->info('🗜️  Compressing backup...');

        $compressedPath = $backupPath . '.gz';
        $command = sprintf('gzip -c %s > %s', escapeshellarg($backupPath), escapeshellarg($compressedPath));

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            unlink($backupPath); // Remove original uncompressed file
            $this->info('✅ Backup compressed successfully');

            return $compressedPath;
        } else {
            $this->warn('⚠️  Compression failed, keeping uncompressed backup');

            return $backupPath;
        }
    }

    private function uploadToCloud(string $backupPath, string $filename): void
    {
        $this->info('☁️  Uploading to cloud storage...');

        try {
            $disk = Storage::disk('s3'); // or your configured cloud disk
            $cloudPath = 'database-backups/' . date('Y/m/') . $filename;

            $disk->put($cloudPath, file_get_contents($backupPath));

            $this->info("✅ Uploaded to cloud: {$cloudPath}");

            LogService::database('Backup uploaded to cloud', [
                'local_path' => $backupPath,
                'cloud_path' => $cloudPath,
                'filename' => $filename,
            ]);
        } catch (\Exception $e) {
            $this->warn('⚠️  Cloud upload failed: ' . $e->getMessage());

            LogService::error('Backup cloud upload failed', $e, [
                'filename' => $filename,
                'local_path' => $backupPath,
            ]);
        }
    }

    private function cleanupOldBackups(): void
    {
        $this->info('🧹 Cleaning up old backups...');

        $backupDir = storage_path('app/backups');
        $retentionDays = 30; // Keep backups for 30 days
        $cutoffTime = Carbon::now()->subDays($retentionDays)->timestamp;

        $files = glob($backupDir . '/*.sql*');
        $deletedCount = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
                $this->line('🗑️  Deleted: ' . basename($file));
            }
        }

        $this->info("✅ Cleanup completed. Deleted {$deletedCount} old backup(s)");

        LogService::database('Old backups cleaned up', [
            'deleted_count' => $deletedCount,
            'retention_days' => $retentionDays,
        ]);
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
