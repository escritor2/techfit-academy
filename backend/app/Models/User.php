<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use BelongsToTenant, SoftDeletes;

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'cpf',
        'age',
        'last_attendance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_attendance' => 'datetime',
        ];
    }

    // ── Relationships ──

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->active()->latest('starts_at');
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function workouts()
    {
        return $this->hasMany(Workout::class)->latest();
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function classBookings()
    {
        return $this->hasMany(ClassBooking::class);
    }

    public function bodyMetrics()
    {
        return $this->hasMany(BodyMetric::class)->orderBy('measured_at', 'desc');
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
                    ->withPivot('earned_at')
                    ->withTimestamps();
    }

    public function diets()
    {
        return $this->hasMany(Diet::class)->latest();
    }

    // ── Scopes ──

    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    // ── Helpers ──

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->active()->exists();
    }
}
