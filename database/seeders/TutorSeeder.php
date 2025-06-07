<?php

namespace Database\Seeders;

use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
// Removed unused imports for Subject, Availability, Hash if they are not used elsewhere in this file specifically for TutorSeeder logic

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with the tutor role
        $tutorUsers = User::where('role', 'tutor')->get();

        $bios = [
            'Experienced and passionate educator with a proven track record of helping students excel. I specialize in creating engaging learning experiences tailored to individual needs.',
            'Dedicated tutor with over 8 years of teaching experience. I believe in building strong foundations and developing critical thinking skills that extend beyond the classroom.',
            'Patient and methodical tutor focused on breaking down complex concepts into easily understandable parts. I enjoy seeing the "aha" moment when students grasp difficult material.',
            'Enthusiastic educator committed to fostering a love of learning. I combine traditional teaching methods with modern technology to create an effective learning environment.',
            'Results-oriented tutor with expertise in preparing students for standardized tests and competitive exams. My students consistently achieve scores in the top percentiles.'
        ];

        $specializations = [
            'Advanced Calculus and Differential Equations',
            'Organic Chemistry and Lab Techniques',
            'Classical and Quantum Physics',
            'Literature Analysis and Essay Writing',
            'Data Structures and Algorithms'
        ];

        if ($tutorUsers->isEmpty()) {
            $this->command->info('TutorSeeder: No users with role \'tutor\' found. Skipping tutor profile creation.');
            return;
        }

        foreach ($tutorUsers as $index => $user) {
            Tutor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $bios[$index % count($bios)],
                    'hourly_rate' => rand(25, 100),
                    'is_available' => true,
                    'experience_years' => rand(2, 15),
                    'specialization' => $specializations[$index % count($specializations)],
                ]
            );
        }
        $this->command->info('TutorSeeder: Processed tutor profiles for ' . $tutorUsers->count() . ' users.');
    }
}
