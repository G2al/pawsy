<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'morning_open',
        'morning_close',
        'afternoon_open',
        'afternoon_close',
        'is_closed',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'morning_open' => 'datetime:H:i',
        'morning_close' => 'datetime:H:i',
        'afternoon_open' => 'datetime:H:i',
        'afternoon_close' => 'datetime:H:i',
        'is_closed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_closed' => false,
    ];

    public function getDayNameAttribute(): string
    {
        return match($this->day_of_week) {
            0 => 'Domenica',
            1 => 'Lunedì',
            2 => 'Martedì',
            3 => 'Mercoledì',
            4 => 'Giovedì',
            5 => 'Venerdì',
            6 => 'Sabato',
            default => 'Sconosciuto',
        };
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeByDay($query, int $day)
    {
        return $query->where('day_of_week', $day);
    }
}