<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VehicleOwnerProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'uuid',

        'user_id',

        'owner_type',

        'business_name',

        'nic_passport',

        'phone',

        'address',

        'country_id',

        'province_id',

        'district_id',

        'city_id',

        'area_id',

        'application_status',

        'verified_by',

        'admin_notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($profile) {
            if (empty($profile->uuid)) {
                $profile->uuid = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}