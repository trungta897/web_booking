<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupProject extends Command
{
    protected $signature = 'project:cleanup {--dry-run : Show what would be cleaned without actually cleaning}';

    protected $description = 'Clean up project files, remove unused assets, and optimize structure';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🧹 Starting project cleanup...');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No files will be actually deleted');
            $this->newLine();
        }

        $cleanupActions = [
            'cleanupTempFiles',
            'cleanupLogFiles',
            'cleanupCacheFiles',
            'cleanupUnusedAssets',
            'cleanupEmptyDirectories',
            'optimizeImages',
            'validateJavaScriptFiles',
        ];

        $totalCleaned = 0;

        foreach ($cleanupActions as $action) {
            $cleaned = $this->$action($dryRun);
            $totalCleaned += $cleaned;
        }

        $this->newLine();
        $this->info('✅ Project cleanup completed!');
        $this->comment("📊 Total items processed: {$totalCleaned}");

        if ($dryRun) {
            $this->warn('🔄 Run without --dry-run to actually perform cleanup');
        }

        return Command::SUCCESS;
    }

    private function cleanupTempFiles($dryRun = false): int
    {
        $this->info('🗑️ Cleaning temporary files...');

        $tempPatterns = [
            storage_path('app/temp/*'),
            storage_path('framework/cache/data/*'),
            storage_path('framework/sessions/*'),
            storage_path('framework/views/*'),
            public_path('hot'),
            base_path('.phpunit.result.cache'),
        ];

        $cleaned = 0;

        foreach ($tempPatterns as $pattern) {
            $files = glob($pattern);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $this->line('  • ' . basename($file));
                    if (!$dryRun) {
                        unlink($file);
                    }
                    $cleaned++;
                } elseif (is_dir($file) && basename($file) !== '.gitignore') {
                    $this->line('  • ' . basename($file) . '/');
                    if (!$dryRun) {
                        File::deleteDirectory($file);
                    }
                    $cleaned++;
                }
            }
        }

        $this->comment("  Cleaned {$cleaned} temporary files");

        return $cleaned;
    }

    private function cleanupLogFiles($dryRun = false): int
    {
        $this->info('📝 Cleaning old log files...');

        $logPath = storage_path('logs');
        $cleaned = 0;

        if (File::exists($logPath)) {
            $logFiles = File::files($logPath);
            $cutoffDate = now()->subDays(30);

            foreach ($logFiles as $file) {
                $fileTime = File::lastModified($file->getPathname());

                if ($fileTime < $cutoffDate->timestamp && $file->getExtension() === 'log') {
                    $this->line('  • ' . $file->getFilename());
                    if (!$dryRun) {
                        File::delete($file->getPathname());
                    }
                    $cleaned++;
                }
            }
        }

        $this->comment("  Cleaned {$cleaned} old log files");

        return $cleaned;
    }

    private function cleanupCacheFiles($dryRun = false): int
    {
        $this->info('💾 Cleaning cache files...');

        $cleaned = 0;

        if (!$dryRun) {
            // Clear application caches
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('route:clear');
            $this->call('view:clear');
            $cleaned += 4;
        } else {
            $this->line('  • Application cache');
            $this->line('  • Configuration cache');
            $this->line('  • Route cache');
            $this->line('  • View cache');
            $cleaned += 4;
        }

        $this->comment("  Cleared {$cleaned} cache types");

        return $cleaned;
    }

    private function cleanupUnusedAssets($dryRun = false): int
    {
        $this->info('🎨 Cleaning unused assets...');

        $cleaned = 0;
        $unusedPatterns = [
            public_path('css/*.map'),
            public_path('js/*.map'),
            public_path('build/assets/*.map'),
        ];

        foreach ($unusedPatterns as $pattern) {
            $files = glob($pattern);
            foreach ($files as $file) {
                $this->line('  • ' . basename($file));
                if (!$dryRun) {
                    unlink($file);
                }
                $cleaned++;
            }
        }

        $this->comment("  Removed {$cleaned} unused asset files");

        return $cleaned;
    }

    private function cleanupEmptyDirectories($dryRun = false): int
    {
        $this->info('📁 Removing empty directories...');

        $searchPaths = [
            storage_path('app'),
            public_path('uploads'),
            public_path('css'),
            public_path('js'),
        ];

        $cleaned = 0;

        foreach ($searchPaths as $path) {
            if (File::exists($path)) {
                $cleaned += $this->removeEmptyDirectories($path, $dryRun);
            }
        }

        $this->comment("  Removed {$cleaned} empty directories");

        return $cleaned;
    }

    private function removeEmptyDirectories($path, $dryRun = false): int
    {
        $cleaned = 0;
        $directories = File::directories($path);

        foreach ($directories as $directory) {
            // Recursively clean subdirectories first
            $cleaned += $this->removeEmptyDirectories($directory, $dryRun);

            // Check if directory is empty (no files, only .gitignore allowed)
            $files = File::files($directory);
            $dirs = File::directories($directory);

            $hasOnlyGitignore = count($files) === 1 &&
                               isset($files[0]) &&
                               basename($files[0]->getFilename()) === '.gitignore';

            if ((empty($files) || $hasOnlyGitignore) && empty($dirs)) {
                $this->line('  • ' . basename($directory));
                if (!$dryRun) {
                    File::deleteDirectory($directory);
                }
                $cleaned++;
            }
        }

        return $cleaned;
    }

    private function optimizeImages($dryRun = false): int
    {
        $this->info('🖼️ Checking image optimization...');

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $imagePaths = [
            public_path('images'),
            public_path('uploads'),
        ];

        $scanned = 0;

        foreach ($imagePaths as $path) {
            if (File::exists($path)) {
                $files = File::allFiles($path);

                foreach ($files as $file) {
                    if (in_array(strtolower($file->getExtension()), $imageExtensions)) {
                        $scanned++;

                        // Check file size (warn if > 2MB)
                        $sizeInMB = $file->getSize() / 1024 / 1024;
                        if ($sizeInMB > 2) {
                            $this->warn('  ⚠️ Large image: ' . $file->getFilename() . " ({$sizeInMB} MB)");
                        }
                    }
                }
            }
        }

        $this->comment("  Scanned {$scanned} image files");

        return $scanned;
    }

    private function validateJavaScriptFiles($dryRun = false): int
    {
        $this->info('📜 Validating JavaScript files...');

        $jsPath = public_path('js');
        $validated = 0;
        $issues = 0;

        if (File::exists($jsPath)) {
            $files = File::allFiles($jsPath);

            foreach ($files as $file) {
                if ($file->getExtension() === 'js') {
                    $content = File::get($file->getPathname());
                    $validated++;

                    // Check for common issues
                    if (strpos($content, '{{ __') !== false) {
                        $this->warn('  ⚠️ Blade syntax found in: ' . $file->getFilename());
                        $issues++;
                    }

                    if (strpos($content, '/*...*/') !== false) {
                        $this->warn('  ⚠️ Placeholder code in: ' . $file->getFilename());
                        $issues++;
                    }

                    if (empty(trim($content))) {
                        $this->warn('  ⚠️ Empty file: ' . $file->getFilename());
                        $issues++;
                    }
                }
            }
        }

        if ($issues > 0) {
            $this->error("  Found {$issues} JavaScript issues");
        } else {
            $this->comment("  All {$validated} JavaScript files look good");
        }

        return $validated;
    }
}
