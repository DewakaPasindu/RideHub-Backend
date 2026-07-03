<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CustomerProfile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'gender',
        'date_of_birth',
        'nic_passport',
        'emergency_contact_name',
        'emergency_contact_phone',
        'preferred_language',
        'profile_completed',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'profile_completed' => 'boolean',
    ];

    /**
     * Automatically generate UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customerProfile) {
            if (empty($customerProfile->uuid)) {
                $customerProfile->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Customer belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}