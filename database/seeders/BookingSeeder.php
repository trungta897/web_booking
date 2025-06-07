<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Added for truncate or delete

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure referential integrity is handled, or use delete() if truncate causes issues.
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // For MySQL if needed
        // Booking::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // For MySQL if needed
        // Simpler alternative if truncate causes issues:
        Booking::query()->delete(); // Deletes all existing bookings
        $this->command->info('BookingSeeder: Deleted all existing bookings.');

        $students = User::where('role', 'student')->get();
        $tutors = Tutor::with('user')->get(); // Eager load user for tutor name access if needed
        $subjects = Subject::all();

        if ($students->isEmpty() || $tutors->isEmpty() || $subjects->isEmpty()) {
            $this->command->info('BookingSeeder: Missing students, tutors, or subjects. Skipping booking creation.');
            return;
        }

        // Status options for bookings
        // $statuses = ['pending', 'accepted', 'completed', 'cancelled', 'rejected']; // Not directly used with this structure

        // Generate bookings across different time periods
        $this->generatePastBookings($students, $tutors, $subjects);
        $this->generateCurrentBookings($students, $tutors, $subjects);
        $this->generateFutureBookings($students, $tutors, $subjects);
        $this->command->info('BookingSeeder: Generated new past, current, and future bookings.');
    }

    /**
     * Generate completed/cancelled bookings in the past
     */
    private function generatePastBookings($students, $tutors, $subjects)
    {
        // Generate 15 past bookings
        for ($i = 0; $i < 15; $i++) {
            $student = $students->random();
            $tutor = $tutors->random();
            $subject = $subjects->random();

            // Random date in past 30 days
            $days = rand(1, 30);
            $startTime = Carbon::now()->subDays($days)->setHour(rand(9, 17))->setMinute(0)->setSecond(0);
            $endTime = (clone $startTime)->addHours(rand(1, 2));

            // Past bookings can be completed or cancelled
            $pastStatuses = ['completed', 'cancelled', 'rejected'];
            $status = $pastStatuses[array_rand($pastStatuses)];

            Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'notes' => 'Need help with ' . $subject->name . ' concepts.',
                'price' => $tutor->hourly_rate * $startTime->diffInHours($endTime),
                'created_at' => $startTime->copy()->subDays(rand(1, 5)),
                'updated_at' => $startTime->copy()->subDays(rand(0, 3)),
            ]);
        }
    }

    /**
     * Generate today's bookings
     */
    private function generateCurrentBookings($students, $tutors, $subjects)
    {
        // Generate 5 current day bookings
        for ($i = 0; $i < 5; $i++) {
            $student = $students->random();
            $tutor = $tutors->random();
            $subject = $subjects->random();

            // Current day, different hours
            $hour = Carbon::now()->hour;

            // Some before current hour, some after
            $startHour = $i < 3 ? rand(max(9, $hour + 1), 20) : rand(9, max(9, $hour - 1));
            $startTime = Carbon::today()->setHour($startHour)->setMinute(0)->setSecond(0);
            $endTime = (clone $startTime)->addHours(rand(1, 2));

            // Today's bookings are pending or accepted
            $status = $i < 3 ? 'accepted' : 'pending';

            Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'notes' => 'Need help with ' . $subject->name . ' homework.',
                'price' => $tutor->hourly_rate * $startTime->diffInHours($endTime),
                'created_at' => $startTime->copy()->subDays(rand(0, 1)),
                'updated_at' => $startTime->copy(),
            ]);
        }
    }

    /**
     * Generate future bookings
     */
    private function generateFutureBookings($students, $tutors, $subjects)
    {
        // Generate 10 future bookings
        for ($i = 0; $i < 10; $i++) {
            $student = $students->random();
            $tutor = $tutors->random();
            $subject = $subjects->random();

            // Random date in future 14 days
            $days = rand(1, 14);
            $startTime = Carbon::now()->addDays($days)->setHour(rand(9, 17))->setMinute(0)->setSecond(0);
            $endTime = (clone $startTime)->addHours(rand(1, 2));

            // Future bookings are pending or accepted
            $status = $i < 6 ? 'accepted' : 'pending';

            Booking::create([
                'student_id' => $student->id,
                'tutor_id' => $tutor->id,
                'subject_id' => $subject->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'notes' => 'Need help with upcoming ' . $subject->name . ' exam.',
                'price' => $tutor->hourly_rate * $startTime->diffInHours($endTime),
                'created_at' => Carbon::now()->subDays(rand(0, 3)),
                'updated_at' => Carbon::now()->subDays(rand(0, 1)),
            ]);
        }
    }
}
