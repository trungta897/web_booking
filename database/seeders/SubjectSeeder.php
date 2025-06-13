<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics', 'description' => 'Learn mathematics from basic to advanced level', 'icon' => 'fa-calculator'],
            ['name' => 'Physics', 'description' => 'Study the nature and properties of matter and energy', 'icon' => 'fa-atom'],
            ['name' => 'Chemistry', 'description' => 'Explore the composition, structure, and properties of substances', 'icon' => 'fa-flask'],
            ['name' => 'Biology', 'description' => 'Study living organisms and their vital processes', 'icon' => 'fa-dna'],
            ['name' => 'English Literature', 'description' => 'Analyze and interpret literary works', 'icon' => 'fa-book'],
            ['name' => 'Computer Science', 'description' => 'Learn programming, algorithms, and information theory', 'icon' => 'fa-laptop-code'],
            ['name' => 'History', 'description' => 'Study past events and their significance', 'icon' => 'fa-monument'],
            ['name' => 'Geography', 'description' => 'Explore the physical features of the earth and human societies', 'icon' => 'fa-globe-americas'],
            ['name' => 'Spanish', 'description' => 'Learn Spanish language and culture', 'icon' => 'fa-language'],
            ['name' => 'French', 'description' => 'Learn French language and culture', 'icon' => 'fa-language'],
            ['name' => 'Economics', 'description' => 'Study the production, distribution, and consumption of goods and services', 'icon' => 'fa-chart-line'],
            ['name' => 'Business Studies', 'description' => 'Learn about organizations, management, and the business environment', 'icon' => 'fa-briefcase'],
            ['name' => 'Music', 'description' => 'Study music theory, performance, and appreciation', 'icon' => 'fa-music'],
            ['name' => 'Art', 'description' => 'Explore visual arts, art history, and creative techniques', 'icon' => 'fa-palette'],
            ['name' => 'Physical Education', 'description' => 'Learn about sports, fitness, and health', 'icon' => 'fa-running'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::updateOrCreate(
                ['name' => $subjectData['name']],
                $subjectData
            );
        }
    }
}
