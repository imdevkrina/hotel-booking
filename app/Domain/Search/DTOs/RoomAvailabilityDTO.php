<?php

declare(strict_types=1);

namespace App\Domain\Search\DTOs;

final readonly class RoomAvailabilityDTO
{
    public function __construct(
        public int     $roomTypeId,
        public string  $roomType,
        public bool    $available,
        public float   $priceRoomOnly,
        public float   $priceBreakfast,
        public float   $originalRoomOnly = 0,
        public float   $originalBreakfast = 0,
        public float   $discountPercent = 0,
        public array   $discountLabels = [],
        public ?string $imageUrl = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $base = [
            'room_type_id'    => $this->roomTypeId,
            'room_type'       => $this->roomType,
            'available'       => $this->available,
            'image_url'       => $this->imageUrl,
        ];

        if ($this->available) {
            return array_merge($base, [
                'price_room_only'     => $this->priceRoomOnly,
                'price_breakfast'     => $this->priceBreakfast,
                'original_room_only'  => $this->originalRoomOnly,
                'original_breakfast'  => $this->originalBreakfast,
                'discount_percent'    => $this->discountPercent,
                'discount_labels'     => $this->discountLabels,
            ]);
        }

        return array_merge($base, ['status' => 'Sold Out']);
    }
}
