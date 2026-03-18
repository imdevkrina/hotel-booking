<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'name',
        'total_rooms',
        'max_adults',
        'breakfast_surcharge',
        'image',
    ];

    protected $casts = [
        'total_rooms'          => 'integer',
        'max_adults'           => 'integer',
        'breakfast_surcharge'  => 'decimal:2',
    ];

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
