<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class District extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'province_id',
        'name',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}