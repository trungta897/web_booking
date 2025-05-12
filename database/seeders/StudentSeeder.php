<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'name' => 'Emma Wilson',
                'email' => 'emma@example.com',
                'bio' => 'High school student interested in mathematics and physics.',
            ],
            [
                'name' => 'James Brown',
                'email' => 'james@example.com',
                'bio' => 'College student majoring in Computer Science.',
            ],
            [
                'name' => 'Sophia Lee',
                'email' => 'sophia@example.com',
                'bio' => 'University student studying English Literature.',
            ],
        ];

        foreach ($students as $studentData) {
            User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'bio' => $studentData['bio'],
            ]);
        }
    }
}
