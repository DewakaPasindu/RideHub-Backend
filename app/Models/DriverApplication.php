<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Core\Traits\HasUuid;
use App\Core\Enums\ApplicationStatus;

class DriverApplication extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'uuid',
        'user_id',
        'application_status',
        'first_name',
        'last_name',
        'nic_passport',
        'date_of_birth',
        'phone',
        'address',
        'driving_license_number',
        'license_expiry_date',
        'years_of_experience',
        'languages',
        'skills',
        'availability',
        'license_document',
        'nic_document',
        'selfie_photo',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'application_status' => ApplicationStatus::class,

        'date_of_birth' => 'date',

        'license_expiry_date' => 'date',

        'reviewed_at' => 'datetime',

        'languages' => 'array',

        'skills' => 'array',
    ];

    /**
     * Applicant.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Reviewing administrator.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}