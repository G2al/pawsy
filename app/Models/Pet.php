<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'sex',
        'breed',
        'size',
        'birth_date',
        'photo',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getAgeAttribute(): ?string
    {
        if (!$this->birth_date) {
            return null;
        }
        
        $years = $this->birth_date->diffInYears(now());
        $months = $this->birth_date->diffInMonths(now()) % 12;
        
        if ($years > 0) {
            return $years . ' ' . ($years === 1 ? 'anno' : 'anni');
        }
        
        return $months . ' ' . ($months === 1 ? 'mese' : 'mesi');
    }

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }
}