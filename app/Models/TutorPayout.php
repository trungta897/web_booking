<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'total_amount',
        'status',
        'bank_account',
        'bank_name',
        'account_holder_name',
        'admin_notes',
        'failure_reason',
        'requested_at',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function payoutItems(): HasMany
    {
        return $this->hasMany(PayoutItem::class, 'payout_id');
    }

    public function bookings()
    {
        return $this->hasManyThrough(
            Booking::class,
            PayoutItem::class,
            'payout_id', // Foreign key on payout_items table
            'id',        // Foreign key on bookings table
            'id',        // Local key on tutor_payouts table
            'booking_id' // Local key on payout_items table
        );
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeProcessed(): bool
    {
        return $this->isPending();
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() || $this->isProcessing();
    }

    // Format helpers
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', '.') . ' VND';
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_COMPLETED => 'Hoàn thành',
            self::STATUS_FAILED => 'Thất bại',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    // Mutators
    public function setTotalAmountAttribute($value)
    {
        $this->attributes['total_amount'] = round($value, 2);
    }
}
