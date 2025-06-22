<?php

namespace Database\Seeders;

use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;

// Removed unused imports for Subject, Availability, Hash if they are not used elsewhere in this file specifically for TutorSeeder logic

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with the tutor role
        $tutorUsers = User::where('role', 'tutor')->get();

        $tutorData = [
            [
                'bio' => 'Experienced Mathematics tutor with a PhD in Applied Mathematics. I specialize in making complex concepts simple and enjoyable. My students consistently achieve excellent results in calculus, algebra, and statistics.',
                'hourly_rate' => 45,
                'experience_years' => 8,
                'specialization' => 'Advanced Calculus and Mathematical Analysis',
            ],
            [
                'bio' => 'Passionate Chemistry educator with 6 years of teaching experience. I help students understand organic and inorganic chemistry through hands-on examples and real-world applications.',
                'hourly_rate' => 40,
                'experience_years' => 6,
                'specialization' => 'Organic Chemistry and Laboratory Techniques',
            ],
            [
                'bio' => 'Physics PhD with expertise in both classical and quantum mechanics. I love breaking down complex physics problems into manageable steps and helping students develop problem-solving skills.',
                'hourly_rate' => 50,
                'experience_years' => 10,
                'specialization' => 'Classical and Quantum Physics',
            ],
            [
                'bio' => 'English Literature graduate with a passion for writing and analysis. I help students improve their essay writing, reading comprehension, and critical thinking skills.',
                'hourly_rate' => 35,
                'experience_years' => 5,
                'specialization' => 'Literature Analysis and Creative Writing',
            ],
            [
                'bio' => 'Computer Science professional with industry experience. I teach programming fundamentals, data structures, and help students prepare for technical interviews.',
                'hourly_rate' => 55,
                'experience_years' => 7,
                'specialization' => 'Programming and Data Structures',
            ],
            [
                'bio' => 'Experienced Biology tutor with a Master\'s degree in Molecular Biology. I make complex biological processes easy to understand through visual aids and interactive learning.',
                'hourly_rate' => 42,
                'experience_years' => 6,
                'specialization' => 'Molecular Biology and Genetics',
            ],
            [
                'bio' => 'Native Spanish speaker with teaching certification. I help students achieve fluency through immersive conversation practice and cultural understanding.',
                'hourly_rate' => 30,
                'experience_years' => 4,
                'specialization' => 'Spanish Language and Culture',
            ],
            [
                'bio' => 'Economics PhD with experience in both academic and business settings. I teach microeconomics, macroeconomics, and help students understand economic principles.',
                'hourly_rate' => 48,
                'experience_years' => 9,
                'specialization' => 'Economics and Business Studies',
            ],
            [
                'bio' => 'French language expert with native fluency. I offer comprehensive French instruction from beginner to advanced levels, focusing on grammar, conversation, and culture.',
                'hourly_rate' => 32,
                'experience_years' => 5,
                'specialization' => 'French Language and Literature',
            ],
            [
                'bio' => 'Statistics and Data Science specialist with industry experience. I help students master statistical concepts, probability, and data analysis using real-world examples.',
                'hourly_rate' => 52,
                'experience_years' => 8,
                'specialization' => 'Statistics and Data Analysis',
            ],
            [
                'bio' => 'Medical Doctor with expertise in anatomy and physiology. I help pre-med students understand complex biological systems and prepare for entrance exams.',
                'hourly_rate' => 65,
                'experience_years' => 12,
                'specialization' => 'Medical Sciences and MCAT Preparation',
            ],
            [
                'bio' => 'History Professor with specialization in World History and American History. I make historical events come alive through storytelling and critical analysis.',
                'hourly_rate' => 38,
                'experience_years' => 15,
                'specialization' => 'World History and Historical Analysis',
            ],
            [
                'bio' => 'Software Engineer with expertise in web development and algorithms. I teach modern programming languages and help students build real-world projects.',
                'hourly_rate' => 58,
                'experience_years' => 6,
                'specialization' => 'Web Development and Software Engineering',
            ],
            [
                'bio' => 'Psychology graduate with research experience. I help students understand psychological theories, research methods, and statistical analysis in psychology.',
                'hourly_rate' => 36,
                'experience_years' => 4,
                'specialization' => 'Psychology and Research Methods',
            ],
            [
                'bio' => 'Accounting professional with CPA certification. I teach financial accounting, managerial accounting, and help students prepare for professional exams.',
                'hourly_rate' => 44,
                'experience_years' => 7,
                'specialization' => 'Accounting and Financial Analysis',
            ],
            [
                'bio' => 'Environmental Science researcher with field experience. I help students understand environmental systems, ecology, and sustainability concepts.',
                'hourly_rate' => 40,
                'experience_years' => 5,
                'specialization' => 'Environmental Science and Ecology',
            ],
            [
                'bio' => 'Music Theory and Piano instructor with conservatory training. I teach music theory, composition, and piano performance for all skill levels.',
                'hourly_rate' => 35,
                'experience_years' => 8,
                'specialization' => 'Music Theory and Piano Performance',
            ],
            [
                'bio' => 'Engineering graduate with specialization in mechanical systems. I help students with engineering mathematics, thermodynamics, and design principles.',
                'hourly_rate' => 50,
                'experience_years' => 6,
                'specialization' => 'Mechanical Engineering and Design',
            ],
            [
                'bio' => 'Art History professor with museum experience. I guide students through art movements, techniques, and cultural contexts of artistic works.',
                'hourly_rate' => 34,
                'experience_years' => 10,
                'specialization' => 'Art History and Visual Culture',
            ],
            [
                'bio' => 'Philosophy PhD with expertise in ethics and logic. I help students develop critical thinking skills and understand philosophical arguments.',
                'hourly_rate' => 42,
                'experience_years' => 9,
                'specialization' => 'Philosophy and Critical Thinking',
            ],
            [
                'bio' => 'Certified SAT/ACT prep specialist with proven track record. I help students improve test scores through targeted practice and strategic techniques.',
                'hourly_rate' => 46,
                'experience_years' => 5,
                'specialization' => 'SAT/ACT Test Preparation',
            ],
            [
                'bio' => 'Geographic Information Systems (GIS) expert with research background. I teach spatial analysis, cartography, and environmental mapping techniques.',
                'hourly_rate' => 48,
                'experience_years' => 7,
                'specialization' => 'Geography and GIS Analysis',
            ],
            [
                'bio' => 'Political Science graduate with campaign experience. I help students understand government systems, political theory, and current affairs analysis.',
                'hourly_rate' => 36,
                'experience_years' => 4,
                'specialization' => 'Political Science and Government',
            ],
            [
                'bio' => 'Nursing instructor with clinical experience. I help nursing students with anatomy, pharmacology, and clinical reasoning skills.',
                'hourly_rate' => 54,
                'experience_years' => 11,
                'specialization' => 'Nursing and Healthcare Sciences',
            ],
            [
                'bio' => 'Business Administration graduate with MBA. I teach business strategy, marketing, and entrepreneurship with real-world case studies.',
                'hourly_rate' => 50,
                'experience_years' => 8,
                'specialization' => 'Business Strategy and Marketing',
            ],
        ];

        if ($tutorUsers->isEmpty()) {
            $this->command->info('TutorSeeder: No users with role \'tutor\' found. Skipping tutor profile creation.');

            return;
        }

        foreach ($tutorUsers as $index => $user) {
            $data = $tutorData[$index % count($tutorData)];

            Tutor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => $data['bio'],
                    'hourly_rate' => $data['hourly_rate'],
                    'is_available' => true,
                    'experience_years' => $data['experience_years'],
                    'specialization' => $data['specialization'],
                ]
            );
        }

        $this->command->info('TutorSeeder: Processed tutor profiles for '.$tutorUsers->count().' users.');
    }
}
