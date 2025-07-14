<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    // Status constants for backward compatibility
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    // Payment status constants
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'student_id',
        'tutor_id',
        'subject_id',
        'start_time',
        'end_time',
        'is_confirmed',
        'is_cancelled',
        'is_completed',
        'cancellation_reason',
        'rejection_reason',
        'rejection_description', // Thêm field này
        'accepted_at', // Thêm field này để track khi tutor accept
        'notes',
        'price',
        'meeting_link',
        'payment_method',
        'vnpay_txn_ref',
        'payment_intent_id',
        'payment_at',
        'payment_metadata',
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
        'payment_metadata' => 'array',
        'payment_at' => 'datetime',
        'is_confirmed' => 'boolean',
        'is_cancelled' => 'boolean',
        'is_completed' => 'boolean',
        // Commission fields
        'platform_fee_percentage' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'tutor_earnings' => 'decimal:2',
        'commission_calculated_at' => 'datetime',
    ];

    // 🎯 BOOLEAN LOGIC HELPERS
    public function isPending(): bool
    {
        return !$this->is_confirmed && !$this->is_cancelled && !$this->is_completed;
    }

    public function isConfirmed(): bool
    {
        return $this->is_confirmed && !$this->is_cancelled && !$this->is_completed;
    }

    public function isCancelled(): bool
    {
        return $this->is_cancelled;
    }

    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    public function isPaid(): bool
    {
        return !is_null($this->payment_at);
    }

    public function canBeCancelled(): bool
    {
        return !$this->is_cancelled && !$this->is_completed;
    }

    public function canBeReviewed(): bool
    {
        return $this->is_completed && !$this->review;
    }

    // 🎯 STATUS DISPLAY HELPERS - Sử dụng accepted_at để xác định trạng thái
    public function getStatusAttribute(): string
    {
        // Logic ưu tiên: completed > cancelled > confirmed > rejected > accepted > pending
        if ($this->is_completed) {
            return 'completed';
        }
        
        if ($this->is_cancelled) {
            return 'cancelled';
        }
        
        if ($this->is_confirmed) {
            return 'confirmed';
        }
        
        // Nếu có rejection_reason thì là rejected
        if (!empty($this->rejection_reason)) {
            return 'rejected';
        }
        
        // 🎯 SỬA LOGIC: Sử dụng accepted_at để xác định trạng thái accepted
        if (!is_null($this->accepted_at)) {
            return 'accepted';
        }
        
        // Mặc định là pending
        return 'pending';
    }

    // Thêm method để kiểm tra trạng thái "accepted" (đã chấp nhận nhưng chưa thanh toán)
    public function isAccepted(): bool
    {
        // Booking được chấp nhận khi:
        // - Không bị cancelled
        // - Không completed  
        // - Không pending (tức là đã có action từ tutor)
        // - Nhưng chưa confirmed (chưa thanh toán)
        return !$this->is_cancelled && 
               !$this->is_completed && 
               !$this->isPending() && 
               !$this->is_confirmed;
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->isPaid()) return 'paid';
        return 'pending';
    }

    // Scopes for boolean logic
    public function scopePending($query)
    {
        return $query->where('is_confirmed', false)
                    ->where('is_cancelled', false)
                    ->where('is_completed', false);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true)
                    ->where('is_cancelled', false)
                    ->where('is_completed', false);
    }

    public function scopeCancelled($query)
    {
        return $query->where('is_cancelled', true);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
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
        return $query->whereNotNull('payment_at');
    }

    // ...existing relationships and methods...
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
        return $query->where('is_confirmed', true)
                    ->where('is_completed', true)
                    ->whereNotNull('payment_at')
                    ->whereNull('payout_id')
                    ->whereNotNull('commission_calculated_at');
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = round($value, 2);
    }
}
