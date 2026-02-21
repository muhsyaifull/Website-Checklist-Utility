<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'reading_date',
        'previous_value',
        'current_value',
        'daily_usage',
        'created_by',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'previous_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'daily_usage' => 'decimal:2',
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

    /**
     * Calculate daily usage automatically.
     */
    public function calculateDailyUsage(): void
    {
        if ($this->current_value !== null && $this->previous_value !== null) {
            $this->daily_usage = $this->current_value - $this->previous_value;
        }
    }

    /**
     * Get the previous reading for the same location.
     */
    public static function getPreviousReading(int $locationId, string $beforeDate): ?self
    {
        return self::where('location_id', $locationId)
            ->where('reading_date', '<', $beforeDate)
            ->orderBy('reading_date', 'desc')
            ->first();
    }
}
