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
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
    ];


    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id'); // Ensure foreign key is specified if not default
    }
}
