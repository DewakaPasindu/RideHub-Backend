<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'booking_id',
        'target_type',
        'vehicle_id',
        'driver_profile_id',
        'rating',
        'comment',
        'status',
        'moderation_note',
        'moderated_at',
        'moderated_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'moderated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Review $review) {
            if (empty($review->uuid)) {
                $review->uuid = (string) Str::uuid();
            }
        });
        
        static::created(function (Review $review) {
            $review->updateTargetStats();
        });
        
        static::updated(function (Review $review) {
            $review->updateTargetStats();
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driverProfile()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_profile_id');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Recalculate and update stats for vehicle/driver profiles.
     */
    public function updateTargetStats()
    {
        if ($this->target_type === 'vehicle' && $this->vehicle_id) {
            $stats = Review::where('target_type', 'vehicle')
                ->where('vehicle_id', $this->vehicle_id)
                ->where('status', 'approved')
                ->selectRaw('count(*) as count, avg(rating) as avg')
                ->first();
            // Trigger update of ratings if relation is set up
            // Since vehicle doesn't store rating on table, we can either calculate it or save to a field
            // Note: in VehicleResource we read reviews_avg_rating and reviews_count, which Laravel eager load supports via withAvg/withCount, but we can also log this.
        } elseif ($this->target_type === 'driver' && $this->driver_profile_id) {
            $stats = Review::where('target_type', 'driver')
                ->where('driver_profile_id', $this->driver_profile_id)
                ->where('status', 'approved')
                ->selectRaw('count(*) as count, avg(rating) as avg')
                ->first();
            if ($stats) {
                $this->driverProfile()->update([
                    'rating' => $stats->avg ?? 5.0,
                    'review_count' => $stats->count ?? 0
                ]);
            }
        }
    }
}
