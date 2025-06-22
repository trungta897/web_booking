<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'transaction_id',
        'payment_method',
        'type',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Constants
    const PAYMENT_METHOD_STRIPE = 'stripe';

    const PAYMENT_METHOD_VNPAY = 'vnpay';

    const PAYMENT_METHOD_PAYPAL = 'paypal';

    const PAYMENT_METHOD_MOMO = 'momo';

    const PAYMENT_METHOD_ZALOPAY = 'zalopay';

    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';

    const TYPE_PAYMENT = 'payment';

    const TYPE_REFUND = 'refund';

    const TYPE_PARTIAL_REFUND = 'partial_refund';

    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_COMPLETED = 'completed';

    const STATUS_FAILED = 'failed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_REFUNDED = 'refunded';

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->whereIn('type', [self::TYPE_REFUND, self::TYPE_PARTIAL_REFUND]);
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefund()
    {
        return in_array($this->type, [self::TYPE_REFUND, self::TYPE_PARTIAL_REFUND]);
    }

    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            self::PAYMENT_METHOD_STRIPE => 'Stripe',
            self::PAYMENT_METHOD_VNPAY => 'VNPay',
            self::PAYMENT_METHOD_PAYPAL => 'PayPal',
            self::PAYMENT_METHOD_MOMO => 'MoMo',
            self::PAYMENT_METHOD_ZALOPAY => 'ZaloPay',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PROCESSING => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            self::STATUS_REFUNDED => 'bg-purple-100 text-purple-800',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
