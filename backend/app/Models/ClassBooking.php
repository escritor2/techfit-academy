<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassBooking extends Model
{
    protected $fillable = [
        'user_id',
        'gym_class_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gymClass()
    {
        return $this->belongsTo(GymClass::class);
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }
}
