<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'country_id',
        'province_id',
        'district_id',
        'city_id',
        'area_id',
        'street_address',
        'postal_code',
        'latitude',
        'longitude',
        'landmark',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}