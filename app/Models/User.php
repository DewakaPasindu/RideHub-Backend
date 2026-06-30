<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles,
        HasUuids,
        SoftDeletes;

    /**
     * Mass Assignable Attributes
     */
    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar',
        'password',
        'status',
    ];

    /**
     * Hidden Attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute Casting
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Use UUID for Route Model Binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Full Name Accessor
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * UUID Generation
     *
     * HasUuids will automatically generate UUIDs.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}