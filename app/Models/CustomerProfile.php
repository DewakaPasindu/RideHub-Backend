<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Core\Traits\HasUuid;

class CustomerProfile extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

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
        'avatar',
        'profile_completed',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'profile_completed' => 'boolean',
    ];

    public function getCompletionPercentageAttribute(): int
    {
        $fields = [

            'gender',

            'date_of_birth',

            'nic_passport',

            'emergency_contact_name',

            'emergency_contact_phone',

            'preferred_language',

        ];

        $completed = 0;

        foreach ($fields as $field) {

            if (!empty($this->{$field})) {
                $completed++;
            }

        }

        return (int) round(
            ($completed / count($fields)) * 100
        );
    }

    
    /**
     * Customer belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}