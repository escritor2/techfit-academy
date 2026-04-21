<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class GymClass extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'instructor_id',
        'schedule',
        'day_of_week',
        'start_time',
        'end_time',
        'capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function bookings()
    {
        return $this->hasMany(ClassBooking::class);
    }

    public function activeBookings()
    {
        return $this->hasMany(ClassBooking::class)->where('status', 'booked');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_bookings')
                    ->wherePivot('status', 'booked');
    }

    public function spotsAvailable(): int
    {
        return $this->capacity - $this->activeBookings()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
