<?php

namespace App\Models;

use App\Core\Enums\CurrencyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VehiclePricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'vehicle_id',

        'base_price',
        'price_per_hour',
        'price_per_day',
        'price_per_km',

        'currency',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'price_per_km' => 'decimal:2',

        'currency' => CurrencyType::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (VehiclePricing $pricing) {
            if (empty($pricing->uuid)) {
                $pricing->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Use UUID for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function vehicle()
    {
        return $this->belongsTo(
            Vehicle::class,
            'vehicle_id'
        );
    }
}