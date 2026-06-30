<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}