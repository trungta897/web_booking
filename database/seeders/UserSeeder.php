<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Tutor users
        $tutors = [
            [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            // Additional tutors
            [
                'name' => 'Priya Patel',
                'email' => 'priya.patel@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Carlos Ramirez',
                'email' => 'carlos.ramirez@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Anna Müller',
                'email' => 'anna.muller@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'David Kim',
                'email' => 'david.kim@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Rachel Green',
                'email' => 'rachel.green@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Prof. Alan Turner',
                'email' => 'alan.turner@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Lisa Wang',
                'email' => 'lisa.wang@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ahmed Hassan',
                'email' => 'ahmed.hassan@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Robert Brown',
                'email' => 'robert.brown@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jennifer Lee',
                'email' => 'jennifer.lee@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Thomas Anderson',
                'email' => 'thomas.anderson@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sophie Martinez',
                'email' => 'sophie.martinez@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Daniel Taylor',
                'email' => 'daniel.taylor@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Amelia Thompson',
                'email' => 'amelia.thompson@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Kevin O\'Brien',
                'email' => 'kevin.obrien@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Isabella Rodriguez',
                'email' => 'isabella.rodriguez@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Benjamin Clark',
                'email' => 'benjamin.clark@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Victoria Singh',
                'email' => 'victoria.singh@example.com',
                'password' => Hash::make('password'),
                'role' => 'tutor',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($tutors as $tutorData) {
            User::updateOrCreate(
                ['email' => $tutorData['email']],
                $tutorData
            );
        }

        // Student users
        $students = [
            [
                'name' => 'Emma Thompson',
                'email' => 'emma@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Noah Garcia',
                'email' => 'noah@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Olivia Martinez',
                'email' => 'olivia@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Liam Rodriguez',
                'email' => 'liam@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sophia Lee',
                'email' => 'sophia@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Alexander Chen',
                'email' => 'alexander.chen@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Maya Johnson',
                'email' => 'maya.johnson@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ethan Williams',
                'email' => 'ethan.williams@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Zoe Davis',
                'email' => 'zoe.davis@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ryan Miller',
                'email' => 'ryan.miller@example.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($students as $studentData) {
            User::updateOrCreate(
                ['email' => $studentData['email']],
                $studentData
            );
        }
    }
}
