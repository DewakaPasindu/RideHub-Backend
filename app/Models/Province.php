<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'country_id',
        'name',
        'code',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}