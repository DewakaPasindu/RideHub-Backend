<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'booking_type',
        'vehicle_id',
        'driver_profile_id',
        'driver_assigned_id',
        'target_name',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'pickup_location',
        'dropoff_location',
        'pickup_lat',
        'pickup_lng',
        'dropoff_lat',
        'dropoff_lng',
        'passenger_count',
        'ac_preference',
        'total_amount',
        'advance_amount',
        'status',
        'rejection_reason',
        'notes',
        'payment_receipt_url',
        'payment_method',
        'trip_started_at',
        'trip_ended_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'cancelled_at',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'passenger_count' => 'integer',
        'total_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'trip_started_at' => 'datetime',
        'trip_ended_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Booking $booking) {
            if (empty($booking->uuid)) {
                $booking->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Driver application acting as profile
    public function driverProfile()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_profile_id');
    }

    public function driverAssigned()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_assigned_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
