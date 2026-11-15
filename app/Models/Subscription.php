<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['portal_id', 'package_id', 'starts_at', 'ends_at', 'grace_days', 'price'];

    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date', 'price' => 'float'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
