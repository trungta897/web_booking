<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tutor_subjects_new')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
