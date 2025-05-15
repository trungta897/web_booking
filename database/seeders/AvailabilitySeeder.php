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
        
        foreach ($tutors as $tutor) {
            // Assign 3-5 days of availability to each tutor
            $numDays = rand(3, 5);
            $availableDays = array_rand(array_flip($days), $numDays);
            
            // Make sure $availableDays is always an array
            if (!is_array($availableDays)) {
                $availableDays = [$availableDays];
            }
            
            foreach ($availableDays as $day) {
                // Morning schedule
                $morningStart = rand(8, 10) . ':00';
                $morningEnd = rand(11, 13) . ':00';
                
                Availability::create([
                    'tutor_id' => $tutor->id,
                    'day_of_week' => $day,
                    'start_time' => $morningStart,
                    'end_time' => $morningEnd,
                    'is_available' => true,
                ]);
                
                // Afternoon schedule (with a 60% chance)
                if (rand(1, 10) <= 6) {
                    $afternoonStart = rand(14, 16) . ':00';
                    $afternoonEnd = rand(17, 19) . ':00';
                    
                    Availability::create([
                        'tutor_id' => $tutor->id,
                        'day_of_week' => $day,
                        'start_time' => $afternoonStart,
                        'end_time' => $afternoonEnd,
                        'is_available' => true,
                    ]);
                }
            }
        }
    }
} 