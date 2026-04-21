<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class ClassBooking extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
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
