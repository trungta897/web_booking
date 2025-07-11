<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_id',
        'booking_id',
        'tutor_earnings',
        'platform_fee_amount',
        'booking_total',
    ];

    protected $casts = [
        'tutor_earnings' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'booking_total' => 'decimal:2',
    ];

    // Relationships
    public function payout(): BelongsTo
    {
        return $this->belongsTo(TutorPayout::class, 'payout_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Helper methods
    public function getFormattedEarningsAttribute(): string
    {
        return number_format($this->tutor_earnings, 0, ',', '.') . ' VND';
    }

    public function getFormattedPlatformFeeAttribute(): string
    {
        return number_format($this->platform_fee_amount, 0, ',', '.') . ' VND';
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->booking_total, 0, ',', '.') . ' VND';
    }

    public function getPlatformFeePercentageAttribute(): float
    {
        if ($this->booking_total <= 0) {
            return 0;
        }

        return round(($this->platform_fee_amount / $this->booking_total) * 100, 2);
    }

    // Mutators
    public function setTutorEarningsAttribute($value)
    {
        $this->attributes['tutor_earnings'] = round($value, 2);
    }

    public function setPlatformFeeAmountAttribute($value)
    {
        $this->attributes['platform_fee_amount'] = round($value, 2);
    }

    public function setBookingTotalAttribute($value)
    {
        $this->attributes['booking_total'] = round($value, 2);
    }
}
