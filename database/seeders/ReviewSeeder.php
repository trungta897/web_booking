<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Review::query()->delete(); // Deletes all existing reviews
        $this->command->info('ReviewSeeder: Deleted all existing reviews.');

        // Get all completed bookings
        $completedBookings = Booking::where('is_completed', true)->get();

        if ($completedBookings->isEmpty()) {
            $this->command->info('ReviewSeeder: No completed bookings found to review. Skipping review creation.');

            return;
        }

        $comments = [
            'Excellent tutor! Very knowledgeable and patient. Made complex concepts easy to understand.',
            'Great teaching style and very responsive to questions. I learned a lot in our session.',
            'Helpful and professional. The session was well-structured and covered everything I needed.',
            'Fantastic tutor who really knows the subject matter. Would definitely book again.',
            'Very supportive and encouraging. Helped me gain confidence in the subject.',
            'Clear explanations and good examples. Made difficult topics more accessible.',
            'Knowledgeable and thorough. The session was focused and productive.',
            'Excellent communication skills and very approachable. Created a comfortable learning environment.',
            'Well-prepared and efficient with our time. Covered more material than I expected.',
            'Patient and understanding. Took time to ensure I fully grasped the concepts.',
        ];

        $reviewsCreated = 0;
        foreach ($completedBookings as $booking) {
            // 80% chance of leaving a review for completed booking
            if (rand(1, 10) <= 8) {
                // Ensure a booking doesn't get multiple reviews from this seeder run
                Review::updateOrCreate(
                    [
                        'booking_id' => $booking->id,
                        // Assuming one review per booking from student to tutor
                        'reviewer_id' => $booking->student_id,
                        'reviewed_user_id' => $booking->tutor_id,
                    ],
                    [
                        'tutor_id' => $booking->tutor_id,
                        'student_id' => $booking->student_id,
                        'rating' => rand(4, 5), // Mostly positive ratings
                        'comment' => $comments[array_rand($comments)],
                        'created_at' => $booking->end_time ? $booking->end_time->addHours(rand(1, 24)) : now()->addHours(rand(1, 24)),
                        'updated_at' => $booking->end_time ? $booking->end_time->addHours(rand(1, 24)) : now()->addHours(rand(1, 24)),
                    ]
                );
                $reviewsCreated++;
            }
        }
        $this->command->info("ReviewSeeder: Created {$reviewsCreated} reviews for completed bookings.");
    }
}
