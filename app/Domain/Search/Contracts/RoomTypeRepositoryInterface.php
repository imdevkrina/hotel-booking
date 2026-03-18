<?php

declare(strict_types=1);

namespace App\Domain\Search\Contracts;

use Illuminate\Support\Collection;

interface RoomTypeRepositoryInterface
{
    /** Returns all room types (cached). */
    public function getAllCached(): Collection;
}
