<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RentalVehicleCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'rental_application_id',
        'recorded_by',
        'inspection_stage',
        'odometer_reading',
        'fuel_level',
        'exterior_condition',
        'interior_condition',
        'existing_damage',
        'condition_description',
    ];

    protected $casts = [
        'odometer_reading' => 'integer',
        'fuel_level' => 'integer',
        'existing_damage' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RentalVehicleCondition $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function rentalApplication()
    {
        return $this->belongsTo(RentalApplication::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function photos()
    {
        return $this->hasMany(RentalConditionPhoto::class, 'condition_id');
    }
}
