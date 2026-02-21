<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'utility_category_id',
        'name',
        'meter_code',
        'description',
    ];

    /**
     * Get the utility category for this location.
     */
    public function utilityCategory(): BelongsTo
    {
        return $this->belongsTo(UtilityCategory::class);
    }

    /**
     * Get all meter readings for this location.
     */
    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    /**
     * Get all token readings for this location.
     */
    public function tokenReadings(): HasMany
    {
        return $this->hasMany(TokenReading::class);
    }

    /**
     * Get the latest meter reading for this location.
     */
    public function latestMeterReading()
    {
        return $this->hasOne(MeterReading::class)->latestOfMany('reading_date');
    }

    /**
     * Get the latest token reading for this location.
     */
    public function latestTokenReading()
    {
        return $this->hasOne(TokenReading::class)->latestOfMany('reading_date');
    }
}
