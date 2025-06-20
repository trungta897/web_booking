<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class AvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tutors = Tutor::all();
        // Remove 'tuesday' from random pool as it will be created for all tutors
        $days = ['monday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        if ($tutors->isEmpty()) {
            $this->command->info('AvailabilitySeeder: No tutors found. Skipping availability creation.');
            return;
        }

        foreach ($tutors as $tutor) {
            // Delete existing availability for this tutor to ensure a fresh set
            $tutor->availability()->delete();

            // Create a specific, reliable availability on Tuesday for ALL tutors for testing
            Availability::create([
                'tutor_id' => $tutor->id,
                'day_of_week' => 'tuesday', // The day you are testing
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',   // A wide slot to cover all potential tests
                'is_available' => true,
            ]);

            // Assign 2-4 other random days of availability to each tutor
            if (empty($days)) {
                continue;
            }
            $numDays = rand(2, min(4, count($days)));
            $availableDayKeys = array_rand($days, $numDays);

            if (!is_array($availableDayKeys)) {
                $availableDayKeys = [$availableDayKeys];
            }

            foreach ($availableDayKeys as $key) {
                $day = $days[$key];
                // Morning schedule
                $morningStartHour = rand(8, 10);
                $morningEndHour = rand(max($morningStartHour + 1, 11), 13);

                Availability::create([
                    'tutor_id' => $tutor->id,
                    'day_of_week' => $day,
                    'start_time' => sprintf('%02d:00:00', $morningStartHour),
                    'end_time' => sprintf('%02d:00:00', $morningEndHour),
                    'is_available' => true,
                ]);

                // Afternoon schedule (with a 60% chance)
                if (rand(1, 10) <= 6) {
                    $afternoonStartHour = rand(14, 16);
                    $afternoonEndHour = rand(max($afternoonStartHour + 1, 17), 19);

                    Availability::create([
                        'tutor_id' => $tutor->id,
                        'day_of_week' => $day,
                        'start_time' => sprintf('%02d:00:00', $afternoonStartHour),
                        'end_time' => sprintf('%02d:00:00', $afternoonEndHour),
                        'is_available' => true,
                    ]);
                }
            }
        }
        $this->command->info("AvailabilitySeeder: Created a fixed 'tuesday' availability for all " . $tutors->count() . " tutors.");
    }
}
