<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Tutor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTestBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:create-test {id=68}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test booking for payment testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookingId = $this->argument('id');

        // Kiểm tra booking đã tồn tại
        if (Booking::find($bookingId)) {
            $this->error("Booking ID {$bookingId} already exists!");
            return 1;
        }

        // Lấy student và tutor đầu tiên
        $student = User::where('role', 'student')->first();
        $tutor = Tutor::with('user')->first();

        if (!$student) {
            $this->error('No student found!');
            return 1;
        }

        if (!$tutor) {
            $this->error('No tutor found!');
            return 1;
        }

        // Tạo booking bằng raw SQL để có thể set ID
        try {
            $startTime = Carbon::now()->addDay()->setHour(14)->setMinute(0)->setSecond(0);
            $endTime = Carbon::now()->addDay()->setHour(16)->setMinute(0)->setSecond(0);

            DB::statement("
                INSERT INTO bookings (
                    id, student_id, tutor_id, subject_id,
                    start_time, end_time, status, payment_status,
                    total_price, hourly_rate, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, NOW(), NOW()
                )
            ", [
                $bookingId,
                $student->id,
                $tutor->id,
                1, // subject_id
                $startTime->format('Y-m-d H:i:s'),
                $endTime->format('Y-m-d H:i:s'),
                'accepted',
                'pending',
                2000000, // 2,000,000 VND
                1000000, // 1,000,000 VND per hour
                'Test booking for payment system'
            ]);

            $this->info("✅ Successfully created test booking!");
            $this->line("ID: {$bookingId}");
            $this->line("Student: {$student->name} (ID: {$student->id})");
            $this->line("Tutor: {$tutor->user->name} (ID: {$tutor->id})");
            $this->line("Status: accepted");
            $this->line("Payment Status: pending");
            $this->line("Total Price: 2,000,000 VND");
            $this->line("Start Time: {$startTime->format('Y-m-d H:i:s')}");
            $this->line("End Time: {$endTime->format('Y-m-d H:i:s')}");
            $this->line("");
            $this->line("🎯 Payment URL: /bookings/{$bookingId}/payment");
            $this->line("📝 You can now test the payment system!");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error creating booking: " . $e->getMessage());
            return 1;
        }
    }
}
