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

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }

    public function pricing()
    {
        return $this->hasOne(VehiclePricing::class);
    }

    public function availability()
    {
        return $this->hasOne(VehicleAvailability::class);
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function insurance()
    {
        return $this->hasOne(VehicleInsurance::class);
    }
}