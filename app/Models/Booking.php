<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tutor_id',
        'subject_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'price',
        'meeting_link',
        'payment_status',
        'payment_intent_id',
        'payment_method',
        'vnpay_txn_ref',
        'exchange_rate',
        'currency',
        'original_amount',
        'payment_metadata',
        'rejection_reason',
        'rejection_description',
        'cancellation_reason',
        'cancellation_description',
        // Commission fields
        'platform_fee_percentage',
        'platform_fee_amount',
        'tutor_earnings',
        'commission_calculated_at',
        'payout_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'original_amount' => 'decimal:2',
        'payment_metadata' => 'array',
        // Commission fields
        'platform_fee_percentage' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'tutor_earnings' => 'decimal:2',
        'commission_calculated_at' => 'datetime',
    ];

    // Add status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_PENDING = 'pending';

    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUS_FAILED = 'failed';

    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    public const PAYMENT_STATUS_PARTIAL_REFUNDED = 'partial_refunded';

    // Add scopes for common queries
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    // Add helper methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaid()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Check if booking is fully paid (comprehensive check).
     */
    public function isFullyPaid()
    {
        // Check payment_status field
        if ($this->payment_status === self::PAYMENT_STATUS_PAID) {
            return true;
        }

        // Check if there are completed payment transactions
        return $this->transactions()
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->exists();
    }

    public function canBeCancelled()
    {
        return $this->isPending() || ($this->isAccepted() && $this->start_time > now());
    }

    public function canBeReviewed()
    {
        return $this->isAccepted() && $this->end_time < now() && !$this->review;
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function getDurationAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // Return absolute value to ensure positive duration
        return abs($start->diffInMinutes($end));
    }

    public function getTotalPriceAttribute()
    {
        return $this->price * ($this->duration / 60);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function completedTransactions()
    {
        return $this->transactions()->completed();
    }

    // Payment method helpers
    public function isStripePayment()
    {
        return $this->payment_method === 'stripe';
    }

    public function isVnpayPayment()
    {
        return $this->payment_method === 'vnpay';
    }

    public function getTotalAmountAttribute()
    {
        return $this->price;
    }

    public function getDisplayAmountAttribute()
    {
        return formatBookingAmount($this);
    }

    public function getPaymentMethodDisplayAttribute()
    {
        $methods = [
            'stripe' => 'Stripe',
            'vnpay' => 'VNPay',
            'paypal' => 'PayPal',
            'cash' => 'Tiền mặt',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    // Commission relationships
    public function payout()
    {
        return $this->belongsTo(TutorPayout::class, 'payout_id');
    }

    public function payoutItem()
    {
        return $this->hasOne(PayoutItem::class, 'booking_id');
    }

    // Commission helper methods
    public function hasCommissionCalculated(): bool
    {
        return !is_null($this->commission_calculated_at);
    }

    public function isIncludedInPayout(): bool
    {
        return !is_null($this->payout_id);
    }

    public function getFormattedPlatformFeeAttribute(): string
    {
        return number_format($this->platform_fee_amount ?? 0, 0, ',', '.') . ' VND';
    }

    public function getFormattedTutorEarningsAttribute(): string
    {
        return number_format($this->tutor_earnings ?? 0, 0, ',', '.') . ' VND';
    }

    // Scope for unpaid earnings (eligible for payout)
    public function scopeEligibleForPayout($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID)
                    ->where('status', 'completed')
                    ->whereNull('payout_id')
                    ->whereNotNull('commission_calculated_at');
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = round($value, 2);
    }
}
