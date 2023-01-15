<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $with = ['spaces'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function spaces(): HasMany
    {
        return $this->hasMany(Space::class);
    }

    protected static function booted()
    {
        static::deleting(function ($booking) {
            $booking->spaces()->delete();
        });
    }
}
