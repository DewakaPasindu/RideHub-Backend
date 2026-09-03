<?php

namespace App\Models;

use App\Core\Enums\ApplicationStatus;
use App\Core\Enums\FuelType;
use App\Core\Enums\TransmissionType;
use App\Core\Enums\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Document;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'vehicle_owner_profile_id',

        'registration_number',
        'make',
        'model',
        'variant',
        'manufacturing_year',
        'color',

        'vehicle_type',
        'fuel_type',
        'transmission',
        'seating_capacity',
        'doors',

        'mileage',
        'chassis_number',
        'engine_number',
        'vin',

        'has_ac',
        'has_gps',
        'description',

        'price_per_day',
        'nearest_town',
        'location_lat',
        'location_lng',
        'features',
        'images',
        'rejection_reason',
        'available_from',
        'available_to',

        'application_status',
        'verified_at',
        'verified_by',
        'admin_notes',
    ];

    protected $casts = [
        'manufacturing_year' => 'integer',
        'mileage' => 'integer',
        'seating_capacity' => 'integer',
        'doors' => 'integer',

        'has_ac' => 'boolean',
        'has_gps' => 'boolean',

        'price_per_day' => 'decimal:2',
        'location_lat' => 'float',
        'location_lng' => 'float',
        'features' => 'array',
        'images' => 'array',
        'available_from' => 'date',
        'available_to' => 'date',

        'vehicle_type' => VehicleType::class,
        'fuel_type' => FuelType::class,
        'transmission' => TransmissionType::class,
        'application_status' => ApplicationStatus::class,

        'verified_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Vehicle $vehicle) {
            if (empty($vehicle->uuid)) {
                $vehicle->uuid = (string) Str::uuid();
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
    |
    */

    public function vehicleOwnerProfile()
    {
        return $this->belongsTo(
            VehicleOwnerProfile::class,
            'vehicle_owner_profile_id'
        );
    }

    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'vehicle_id');
    }
}