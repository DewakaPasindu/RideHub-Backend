<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DriverAvailability extends Model
{
    use HasFactory;

    protected $table = 'driver_availability';

    protected $fillable = [
        'uuid',
        'driver_profile_id',
        'date',
        'is_available',
        'start_time',
        'end_time',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_available' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (DriverAvailability $availability) {
            if (empty($availability->uuid)) {
                $availability->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function driverProfile()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_profile_id');
    }
}
