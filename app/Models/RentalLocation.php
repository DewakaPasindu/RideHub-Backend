<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_application_id',
        'latitude',
        'longitude',
        'accuracy',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'recorded_at' => 'datetime',
    ];

    public $timestamps = true; // table has created_at/updated_at

    public function rentalApplication()
    {
        return $this->belongsTo(RentalApplication::class);
    }
}
