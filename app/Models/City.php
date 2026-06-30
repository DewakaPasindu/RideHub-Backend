<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'district_id',
        'name',
        'latitude',
        'longitude',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}