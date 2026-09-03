<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RentalConditionPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'condition_id',
        'photo_type',
        'file_path',
        'latitude',
        'longitude',
        'captured_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'captured_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RentalConditionPhoto $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function condition()
    {
        return $this->belongsTo(RentalVehicleCondition::class, 'condition_id');
    }
}
