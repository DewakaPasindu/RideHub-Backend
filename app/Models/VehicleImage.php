<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VehicleImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'vehicle_id',

        'image_type',

        'original_name',
        'stored_name',
        'disk',
        'file_path',
        'mime_type',
        'extension',
        'file_size',

        'is_primary',
        'sort_order',

        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (VehicleImage $image) {
            if (empty($image->uuid)) {
                $image->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function vehicle()
    {
        return $this->belongsTo(
            Vehicle::class,
            'vehicle_id'
        );
    }

    public function uploader()
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}