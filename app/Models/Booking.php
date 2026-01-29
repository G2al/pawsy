<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pet_id',
        'service_id',
        'booking_date',
        'time_slot',
        'duration',
        'price',
        'status',
        'payment_status',
        'notes',
        'admin_notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'time_slot' => 'datetime:H:i',
        'duration' => 'integer',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', Carbon::today())
                    ->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopePast($query)
    {
        return $query->where('booking_date', '<', Carbon::today());
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function getFormattedPriceAttribute(): string
    {
        return '€ ' . number_format($this->price, 2, ',', '.');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->booking_date->format('d/m/Y');
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->time_slot->format('H:i');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'In Attesa',
            'confirmed' => 'Confermata',
            'in_progress' => 'In Corso',
            'completed' => 'Completata',
            'cancelled' => 'Cancellata',
            'no_show' => 'Non Presentato',
            default => 'Sconosciuto',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'unpaid' => 'Non Pagato',
            'paid' => 'Pagato',
            default => 'Sconosciuto',
        };
    }
}