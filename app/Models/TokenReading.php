<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'reading_date',
        'token_value',
        'indicator_color',
        'created_by',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'token_value' => 'decimal:2',
    ];

    /**
     * Get the location for this reading.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the user who created this reading.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
