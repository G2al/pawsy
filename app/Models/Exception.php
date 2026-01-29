<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Exception extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'is_closed',
        'morning_open',
        'morning_close',
        'afternoon_open',
        'afternoon_close',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'morning_open' => 'datetime:H:i',
        'morning_close' => 'datetime:H:i',
        'afternoon_open' => 'datetime:H:i',
        'afternoon_close' => 'datetime:H:i',
        'is_closed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_closed' => true,
    ];

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeFuture($query)
    {
        return $query->where('date', '>=', Carbon::today());
    }

    public function scopePast($query)
    {
        return $query->where('date', '<', Carbon::today());
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d/m/Y');
    }
}