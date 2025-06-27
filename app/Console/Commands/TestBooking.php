<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class TestBooking extends Command
{
    protected $signature = 'test:booking {id}';
    protected $description = 'Test booking access';

    public function handle()
    {
        $id = $this->argument('id');

        try {
            $booking = Booking::findOrFail($id);
            $this->info("✅ Booking found: ID {$booking->id}");
            $this->info("📊 Status: {$booking->status}");
            $this->info("💰 Price: {$booking->price}");
            $this->info("💳 Payment Status: {$booking->payment_status}");
            $this->info("👥 Student ID: {$booking->student_id}");
            $this->info("👨‍🏫 Tutor ID: {$booking->tutor_id}");

            if ($booking->tutor) {
                $this->info("👤 Tutor User ID: {$booking->tutor->user_id}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
