<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LocationTracking extends Model
{
    use HasFactory;

    protected $table = 'location_tracking';

    protected $fillable = [
        'uuid',
        'entity_type',
        'entity_id',
        'entity_uuid',
        'lat',
        'lng',
        'accuracy',
        'heading',
        'speed',
        'recorded_at',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'accuracy' => 'float',
        'heading' => 'float',
        'speed' => 'float',
        'recorded_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (LocationTracking $tracking) {
            if (empty($tracking->uuid)) {
                $tracking->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
