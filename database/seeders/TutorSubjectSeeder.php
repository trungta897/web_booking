<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Database\Seeder;

class TutorSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::all();
        $tutors = Tutor::with('user')->get();

        if ($subjects->isEmpty() || $tutors->isEmpty()) {
            $this->command->warn('No tutors or subjects found. Please seed users, tutors, and subjects first.');

            return;
        }

        // Find the 2 highest-priced tutors
        $topTutors = $tutors->sortByDesc('hourly_rate')->take(2)->pluck('id')->toArray();

        foreach ($tutors as $tutor) {
            $rate = floatval($tutor->hourly_rate);
            $subjectCount = 1;
            if ($rate <= 25) {
                // Basic
                $subjectCount = rand(1, 2);
            } elseif ($rate > 25 && $rate <= 40) {
                // Standard
                $subjectCount = 3;
            } elseif ($rate > 40) {
                // Premium
                $subjectCount = rand(4, 5);
            }
            // Ensure top 2 tutors get at least 4 subjects (up to 5 if premium)
            if (in_array($tutor->id, $topTutors)) {
                $subjectCount = max($subjectCount, 4);
                if ($rate > 40) {
                    $subjectCount = rand(4, 5); // Premium top tutors can get up to 5
                }
            }
            $randomSubjects = $subjects->shuffle()->unique('id')->take($subjectCount);
            $syncData = [];
            foreach ($randomSubjects as $subject) {
                $syncData[$subject->id] = [
                    'hourly_rate' => $tutor->hourly_rate ?? 50.00,
                    'description' => 'Proficient in '.$subject->name,
                ];
            }
            $tutor->subjects()->sync($syncData);
            $this->command->info("Assigned $subjectCount subjects to tutor '{$tutor->user->name}' (ID: {$tutor->id}, Rate: ".$tutor->hourly_rate.').');
        }
        $this->command->info('Tutors now have subjects assigned based on pricing.');
    }
}
