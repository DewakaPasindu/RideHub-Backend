<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RentalApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'booking_id',
        'customer_id',
        'vehicle_id',
        'status',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'id_type',
        'id_number',
        'driving_license_number',
        'license_expiry_date',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'return_address',
        'return_latitude',
        'return_longitude',
        'start_at',
        'end_at',
        'passenger_count',
        'luggage_requirement',
        'rental_purpose',
        'additional_requirements',
        'more_info_reason',
        'submitted_at',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'license_expiry_date' => 'date:Y-m-d',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'pickup_latitude' => 'float',
        'pickup_longitude' => 'float',
        'return_latitude' => 'float',
        'return_longitude' => 'float',
        'passenger_count' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RentalApplication $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function documents()
    {
        return $this->hasMany(RentalDocument::class);
    }

    public function conditions()
    {
        return $this->hasMany(RentalVehicleCondition::class);
    }

    public function locations()
    {
        return $this->hasMany(RentalLocation::class);
    }

    public function handover()
    {
        return $this->hasOne(RentalHandover::class);
    }
}
