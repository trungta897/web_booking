<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'tutor_profile_id', // Removed
        'tutor_id',
        'institution',
        'degree',
        'field_of_study',
        'start_year',
        'end_year',
        'description',
        'year',
        'images', // Only keep the images array field
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'images' => 'array',
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id'); // Ensure foreign key is specified if not default
    }

    /**
     * Get all images for this education record (from images array only)
     */
    public function getAllImages(): array
    {
        // Only use the images array field
        if ($this->images && is_array($this->images)) {
            return $this->images;
        }

        return [];
    }

    /**
     * Check if this education record has any images
     */
    public function hasImages(): bool
    {
        return !empty($this->getAllImages());
    }
}
