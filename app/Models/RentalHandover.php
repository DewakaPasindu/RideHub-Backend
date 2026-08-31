<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RentalHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'rental_application_id',
        'status',
        'customer_confirmed_at',
        'owner_confirmed_at',
        'handover_at',
        'handover_latitude',
        'handover_longitude',
    ];

    protected $casts = [
        'customer_confirmed_at' => 'datetime',
        'owner_confirmed_at' => 'datetime',
        'handover_at' => 'datetime',
        'handover_latitude' => 'float',
        'handover_longitude' => 'float',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RentalHandover $model) {
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
