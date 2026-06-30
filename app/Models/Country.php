<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'iso2',
        'iso3',
        'phone_code',
        'is_active',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }
}