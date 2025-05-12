<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'description' => 'Algebra, Calculus, Geometry, Statistics'],
            ['name' => 'Physics', 'description' => 'Mechanics, Thermodynamics, Electromagnetism'],
            ['name' => 'Chemistry', 'description' => 'Organic Chemistry, Inorganic Chemistry, Physical Chemistry'],
            ['name' => 'Biology', 'description' => 'Anatomy, Physiology, Genetics, Ecology'],
            ['name' => 'English', 'description' => 'Literature, Grammar, Writing, Reading Comprehension'],
            ['name' => 'History', 'description' => 'World History, American History, European History'],
            ['name' => 'Computer Science', 'description' => 'Programming, Data Structures, Algorithms'],
            ['name' => 'Economics', 'description' => 'Microeconomics, Macroeconomics, Business Economics'],
            ['name' => 'Psychology', 'description' => 'General Psychology, Developmental Psychology'],
            ['name' => 'Spanish', 'description' => 'Spanish Language, Grammar, Conversation'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
