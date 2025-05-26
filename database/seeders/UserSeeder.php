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
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

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
        ];

        foreach ($tutors as $tutor) {
            User::create($tutor);
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
        ];

        foreach ($students as $student) {
            User::create($student);
        }
    }
}
