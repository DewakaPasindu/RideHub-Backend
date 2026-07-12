<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DriverApplication;

class Area extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'city_id',
        'name',
        'latitude',
        'longitude',
    ];
    public function uniqueIds(): array
{
    return ['uuid'];
}

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function driverApplications()
    {
        return $this->hasMany(DriverApplication::class);
    }
}