<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DriverApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'application_status',

        'first_name',
        'last_name',
        'nic_passport',
        'date_of_birth',
        'gender',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',

        'driving_license_number',
        'license_classes',
        'license_expiry_date',

        'vehicle_types',
        'years_of_experience',
        'languages',
        'skills',

        'area_id',
        'availability',

        'license_document',
        'nic_document',
        'selfie_photo',

        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'license_expiry_date' => 'date',
        'reviewed_at' => 'datetime',

        'languages' => 'array',
        'skills' => 'array',
        'license_classes' => 'array',
        'vehicle_types' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            if (empty($application->uuid)) {
                $application->uuid = (string) Str::uuid();
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->application_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->application_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->application_status === 'rejected';
    }

    public function requiresMoreInfo(): bool
    {
        return $this->application_status === 'more_info_required';
    }
}