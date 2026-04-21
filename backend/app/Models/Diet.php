<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Diet extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'goal',
        'daily_calories',
        'protein_grams',
        'carbs_grams',
        'fats_grams',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
