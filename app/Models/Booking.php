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
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'price' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'original_amount' => 'decimal:2',
        'payment_metadata' => 'array',
    ];

    // Add status constants
    const STATUS_PENDING = 'pending';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_REJECTED = 'rejected';

    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_STATUS_PENDING = 'pending';

    const PAYMENT_STATUS_PAID = 'paid';

    const PAYMENT_STATUS_FAILED = 'failed';

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

    public function canBeCancelled()
    {
        return $this->isPending() || ($this->isAccepted() && $this->start_time > now());
    }

    public function canBeReviewed()
    {
        return $this->isAccepted() && $this->end_time < now() && ! $this->review;
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
        return Carbon::parse($this->start_time)->diffInMinutes(Carbon::parse($this->end_time));
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
        // Get current locale
        $locale = app()->getLocale();

        // If Vietnamese locale and price is in USD, convert to VND
        if ($locale === 'vi' && ($this->currency === 'USD' || !$this->currency)) {
            $vndAmount = (float)$this->price * 25000; // 1 USD = 25,000 VND
            return number_format($vndAmount, 0, ',', '.') . ' ₫';
        }

        // If already in VND
        if ($this->currency === 'VND') {
            return number_format((float)$this->price, 0, ',', '.') . ' ₫';
        }

        // Default to USD format
        return '$' . number_format((float)$this->price, 2);
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

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = round($value, 2);
    }
}
