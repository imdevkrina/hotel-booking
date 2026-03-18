<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'type',
        'min_nights',
        'days_before_checkin',
        'percentage',
        'active',
    ];

    protected $casts = [
        'min_nights'          => 'integer',
        'days_before_checkin' => 'integer',
        'percentage'          => 'decimal:2',
        'active'              => 'boolean',
    ];
}
