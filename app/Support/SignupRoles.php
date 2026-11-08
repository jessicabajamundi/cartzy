<?php

namespace App\Support;

use App\Models\Rider;

/**
 * The choices on the sign-up modal's "Sign up as..." step. Buyer, Seller and Logistics use the sign-up form;
 * Rider (Last Mile: hub to buyer) and Courier (First Mile: seller to hub) use the rider application.
 * Icons are SVG path markup in the same style as the login modal's role list.
 */
class SignupRoles
{
    public static function all(): array
    {
        return [
            'buyer' => [
                'label' => 'Buyer',
                'description' => 'Shop, track orders and save favourites',
                'icon' => '<path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 016 0"/>',
            ],
            'seller' => [
                'label' => 'Seller',
                'description' => 'Open a store and sell your products',
                'icon' => '<path d="M4 10l1.5-5h13L20 10"/><path d="M4 10a2.7 2.7 0 005.3 0 2.7 2.7 0 005.4 0 2.7 2.7 0 005.3 0"/><path d="M5 11v9h14v-9"/><path d="M10 20v-5h4v5"/>',
            ],
            'logistics' => [
                'label' => 'Logistics',
                'description' => 'Run a delivery hub with your own couriers',
                'icon' => '<path d="M3 7l9-4 9 4v10l-9 4-9-4V7z"/><path d="M3 7l9 4 9-4"/><path d="M12 11v10"/>',
            ],
            'rider' => [
                'label' => 'Rider',
                'description' => 'Last Mile: deliver parcels from the hub to buyers',
                'icon' => '<circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M6 17l4-8h5l3 8"/><path d="M8 9h4"/>',
                'url' => route('register.rider', ['type' => Rider::LAST_MILE]),
            ],
            'courier' => [
                'label' => 'Courier',
                'description' => 'First Mile: collect parcels from sellers for the hub',
                'icon' => '<path d="M3 6h11v11H3z"/><path d="M14 10h4l3 3.5V17h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
                'url' => route('register.rider', ['type' => Rider::FIRST_MILE]),
            ],
        ];
    }

    /** The chooser key for a rider application of this courier type. */
    public static function forCourierType(?string $type): string
    {
        return $type === Rider::FIRST_MILE ? 'courier' : 'rider';
    }
}
