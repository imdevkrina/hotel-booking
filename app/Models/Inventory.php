<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'room_type_id',
        'date',
        'price_1_person',
        'price_2_persons',
        'price_3_persons',
    ];

    protected $casts = [
        'date'            => 'date:Y-m-d',
        'price_1_person'  => 'decimal:2',
        'price_2_persons' => 'decimal:2',
        'price_3_persons' => 'decimal:2',
        'room_type_id'    => 'integer',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
