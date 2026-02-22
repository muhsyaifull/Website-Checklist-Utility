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
        'top_up_amount',
        'indicator_color',
        'created_by',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'token_value' => 'decimal:2',
        'top_up_amount' => 'decimal:2',
    ];

    protected $appends = ['total_saldo'];

    /**
     * Get total saldo (sisa saldo + isi ulang).
     */
    public function getTotalSaldoAttribute(): ?float
    {
        if ($this->token_value === null) {
            return null;
        }
        return (float) $this->token_value + (float) ($this->top_up_amount ?? 0);
    }

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
