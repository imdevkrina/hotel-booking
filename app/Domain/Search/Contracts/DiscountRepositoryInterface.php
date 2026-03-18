<?php

declare(strict_types=1);

namespace App\Domain\Search\Contracts;

use Illuminate\Support\Collection;

interface DiscountRepositoryInterface
{
    /** Returns all active discounts (cached). */
    public function getAllActiveCached(): Collection;
}
