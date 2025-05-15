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
        $tutors = Tutor::all();
        $subjects = Subject::all()->pluck('id')->toArray();

        foreach ($tutors as $tutor) {
            // Assign 3-5 random subjects to each tutor
            $numSubjects = rand(3, 5);
            $subjectIds = array_rand(array_flip($subjects), $numSubjects);
            
            // Make sure $subjectIds is always an array
            if (!is_array($subjectIds)) {
                $subjectIds = [$subjectIds];
            }
            
            $tutor->subjects()->attach($subjectIds);
        }
    }
} 