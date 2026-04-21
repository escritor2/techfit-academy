<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings'  => 'array',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function gymClasses()
    {
        return $this->hasMany(GymClass::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ── Helpers ──

    public function getPrimaryColor(): string
    {
        return $this->settings['primary_color'] ?? '#00f2ff';
    }

    public function getLogoUrl(): ?string
    {
        return $this->settings['logo_url'] ?? null;
    }
}
