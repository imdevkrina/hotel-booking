<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'room_type_id',
        'check_in',
        'check_out',
        'guest_count',
        'meal_plan',
        'rooms_booked',
    ];

    protected $casts = [
        'check_in'      => 'date:Y-m-d',
        'check_out'     => 'date:Y-m-d',
        'guest_count'   => 'integer',
        'rooms_booked'  => 'integer',
        'room_type_id'  => 'integer',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
