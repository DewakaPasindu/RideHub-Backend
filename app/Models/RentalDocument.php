<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RentalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'rental_application_id',
        'document_type',
        'document_path',
        'original_filename',
        'mime_type',
        'file_size',
        'verification_status',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RentalDocument $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function rentalApplication()
    {
        return $this->belongsTo(RentalApplication::class);
    }
}
