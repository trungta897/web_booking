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
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        if ($tutors->isEmpty()) {
            $this->command->info('AvailabilitySeeder: No tutors found. Skipping availability creation.');
            return;
        }

        foreach ($tutors as $tutor) {
            // Delete existing availability for this tutor to ensure a fresh set
            $tutor->availability()->delete();

            // Assign 3-5 days of availability to each tutor
            if (empty($days)) { // Should not happen with the predefined $days array
                continue;
            }
            $numDays = rand(3, min(5, count($days))); // Ensure rand max is not greater than count of days
            $availableDayKeys = array_rand($days, $numDays);

            // Make sure $availableDayKeys is always an array
            if (!is_array($availableDayKeys)) {
                $availableDayKeys = [$availableDayKeys];
            }

            foreach ($availableDayKeys as $key) {
                $day = $days[$key];
                // Morning schedule
                $morningStartHour = rand(8, 10);
                $morningEndHour = rand(max($morningStartHour + 1, 11), 13); // Ensure end is after start

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
                    $afternoonEndHour = rand(max($afternoonStartHour + 1, 17), 19); // Ensure end is after start

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
        $this->command->info('AvailabilitySeeder: Processed availability for ' . $tutors->count() . ' tutors.');
    }
}
