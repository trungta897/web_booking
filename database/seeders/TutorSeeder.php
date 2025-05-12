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
    public function run(): void
    {
        $tutors = [
            [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'bio' => 'Experienced mathematics tutor with 5 years of teaching experience.',
                'hourly_rate' => 45.00,
                'subjects' => ['Mathematics', 'Physics'],
                'availability' => [
                    ['day' => 'monday', 'start' => '09:00', 'end' => '17:00'],
                    ['day' => 'wednesday', 'start' => '09:00', 'end' => '17:00'],
                    ['day' => 'friday', 'start' => '09:00', 'end' => '17:00'],
                ],
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'bio' => 'English literature specialist with a passion for teaching.',
                'hourly_rate' => 40.00,
                'subjects' => ['English', 'History'],
                'availability' => [
                    ['day' => 'tuesday', 'start' => '10:00', 'end' => '18:00'],
                    ['day' => 'thursday', 'start' => '10:00', 'end' => '18:00'],
                    ['day' => 'saturday', 'start' => '10:00', 'end' => '15:00'],
                ],
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael@example.com',
                'bio' => 'Computer Science expert with industry experience.',
                'hourly_rate' => 50.00,
                'subjects' => ['Computer Science', 'Mathematics'],
                'availability' => [
                    ['day' => 'monday', 'start' => '13:00', 'end' => '21:00'],
                    ['day' => 'wednesday', 'start' => '13:00', 'end' => '21:00'],
                    ['day' => 'friday', 'start' => '13:00', 'end' => '21:00'],
                ],
            ],
        ];

        foreach ($tutors as $tutorData) {
            // Create user account
            $user = User::create([
                'name' => $tutorData['name'],
                'email' => $tutorData['email'],
                'password' => Hash::make('password'),
                'role' => 'tutor',
            ]);

            // Create tutor profile
            $tutor = Tutor::create([
                'user_id' => $user->id,
                'bio' => $tutorData['bio'],
                'hourly_rate' => $tutorData['hourly_rate'],
            ]);

            // Attach subjects
            $subjectIds = Subject::whereIn('name', $tutorData['subjects'])->pluck('id');
            $tutor->subjects()->attach($subjectIds);

            // Create availability
            foreach ($tutorData['availability'] as $slot) {
                Availability::create([
                    'tutor_id' => $tutor->id,
                    'day_of_week' => $slot['day'],
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'is_available' => true,
                ]);
            }
        }
    }
}
