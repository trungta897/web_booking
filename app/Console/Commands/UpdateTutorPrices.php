<?php

namespace App\Console\Commands;

use App\Models\Tutor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTutorPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tutors:update-prices {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tutor hourly rates to new VND pricing structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('📊 Current tutor pricing analysis:');

        // Get all tutors and their current rates
        $tutors = Tutor::with('user')->orderBy('hourly_rate')->get();

        if ($tutors->isEmpty()) {
            $this->error('❌ No tutors found in database');
            return 1;
        }

        $this->info("Found {$tutors->count()} tutors");
        $this->newLine();

                // Show current prices
        $this->table(
            ['ID', 'Name', 'Current Rate (VND)', 'New Rate (VND)', 'Category'],
            $tutors->map(function ($tutor, $index) use ($tutors) {
                $newRate = $this->calculateNewRate($index, $tutors->count());
                $category = $this->getPriceCategory($newRate);

                return [
                    $tutor->id,
                    $tutor->user->name,
                    number_format($tutor->hourly_rate, 0, '.', ',') . ' VND',
                    number_format($newRate, 0, '.', ',') . ' VND',
                    $category
                ];
            })->toArray()
        );

        if ($dryRun) {
            $this->info('✅ Dry run completed. Use --dry-run=false to apply changes.');
            return 0;
        }

        if (!$this->confirm('Do you want to proceed with updating tutor prices?')) {
            $this->info('❌ Operation cancelled.');
            return 0;
        }

        $this->info('🚀 Updating tutor prices...');

        DB::beginTransaction();

        try {
            $updated = 0;

            foreach ($tutors as $index => $tutor) {
                $newRate = $this->calculateNewRate($index, $tutors->count());

                // Update tutor's base hourly rate
                $tutor->update(['hourly_rate' => $newRate]);

                // Update hourly rates in subject_tutor pivot table
                DB::table('subject_tutor')
                    ->where('tutor_id', $tutor->id)
                    ->update(['hourly_rate' => $newRate]);

                $updated++;
                $this->info("✅ Updated tutor {$tutor->id} ({$tutor->user->name}): " . number_format($newRate, 0, ',', '.') . " VND");
            }

            DB::commit();

            $this->newLine();
            $this->info("🎉 Successfully updated {$updated} tutors with new pricing structure!");
            $this->newLine();
            $this->info('📋 New pricing structure:');
            $this->info('• Cơ bản: 80,000 - 100,000 VND');
            $this->info('• Trung bình: 110,000 - 200,000 VND');
            $this->info('• Cao cấp: 210,000 - 300,000 VND');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error updating prices: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

        /**
     * Calculate new rate based on tutor position (evenly distributed across 3 tiers)
     */
    private function calculateNewRate(int $index, int $totalTutors): float
    {
        $tierSize = ceil($totalTutors / 3);

        if ($index < $tierSize) {
            // Tier 1: Basic (80,000 - 100,000 VND) - Fixed rates
            $basicRates = [80000, 85000, 90000, 95000, 100000];
            return $basicRates[$index % count($basicRates)];
        } elseif ($index < $tierSize * 2) {
            // Tier 2: Standard (110,000 - 200,000 VND) - Fixed rates
            $standardRates = [110000, 120000, 140000, 160000, 180000, 200000];
            $adjustedIndex = $index - $tierSize;
            return $standardRates[$adjustedIndex % count($standardRates)];
        } else {
            // Tier 3: Premium (210,000 - 300,000 VND) - Fixed rates
            $premiumRates = [210000, 230000, 250000, 270000, 290000, 300000];
            $adjustedIndex = $index - ($tierSize * 2);
            return $premiumRates[$adjustedIndex % count($premiumRates)];
        }
    }

    /**
     * Get price category name
     */
    private function getPriceCategory(float $rate): string
    {
        if ($rate <= 100000) {
            return 'Cơ bản';
        } elseif ($rate <= 200000) {
            return 'Trung bình';
        } else {
            return 'Cao cấp';
        }
    }
}
