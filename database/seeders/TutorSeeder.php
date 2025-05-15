<?php

namespace Database\Seeders;

use App\Models\Tutor;
use App\Models\User;
use App\Models\Subject;
use App\Models\Availability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        foreach ($tutorUsers as $index => $user) {
            // Create tutor profile
            Tutor::create([
                'user_id' => $user->id,
                'bio' => $bios[$index % count($bios)],
                'hourly_rate' => rand(25, 100),
                'is_available' => true,
                'experience_years' => rand(2, 15),
                'specialization' => $specializations[$index % count($specializations)],
            ]);
        }
    }
}
