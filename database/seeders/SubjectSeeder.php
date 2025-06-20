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
            ['name' => 'Statistics', 'description' => 'Learn statistical analysis, probability, and data interpretation', 'icon' => 'fa-chart-bar'],
            ['name' => 'Psychology', 'description' => 'Study human behavior, mental processes, and research methods', 'icon' => 'fa-brain'],
            ['name' => 'Accounting', 'description' => 'Learn financial accounting, bookkeeping, and financial analysis', 'icon' => 'fa-coins'],
            ['name' => 'Engineering', 'description' => 'Study engineering principles, design, and problem-solving', 'icon' => 'fa-cogs'],
            ['name' => 'Environmental Science', 'description' => 'Explore environmental systems, ecology, and sustainability', 'icon' => 'fa-leaf'],
            ['name' => 'Philosophy', 'description' => 'Study philosophical thought, ethics, and critical reasoning', 'icon' => 'fa-scroll'],
            ['name' => 'Political Science', 'description' => 'Learn about government systems, politics, and public policy', 'icon' => 'fa-landmark'],
            ['name' => 'Nursing', 'description' => 'Study healthcare, anatomy, physiology, and patient care', 'icon' => 'fa-user-nurse'],
            ['name' => 'Medicine', 'description' => 'Learn medical sciences, anatomy, and healthcare fundamentals', 'icon' => 'fa-heartbeat'],
            ['name' => 'Test Preparation', 'description' => 'Prepare for standardized tests like SAT, ACT, GRE, and more', 'icon' => 'fa-graduation-cap'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::updateOrCreate(
                ['name' => $subjectData['name']],
                $subjectData
            );
        }
    }
}
