<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class BodyMetric extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'weight',
        'height',
        'body_fat',
        'chest',
        'waist',
        'biceps',
        'thigh',
        'measured_at',
    ];

    protected function casts(): array
    {
        return [
            'measured_at' => 'date',
            'weight' => 'float',
            'height' => 'float',
            'body_fat' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
