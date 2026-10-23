<?php

/*
 * Location-based shipping, Shopee/Lazada style: every store ships its own parcel, priced by how far
 * it travels from the store's pickup province to the buyer's province. Fees per zone are edited by
 * the admin (shipping_rates table); this file only maps provinces to island groups.
 *
 * Province names come from the PSGC address picker. They are compared ignoring case, spaces and
 * punctuation, so "Agusan Del Sur", "agusan del sur" and "Agusan-del-Sur" all match.
 */
return [
    // Stores without a pickup address (e.g. catalog stores) ship from here.
    'default_origin' => 'Metro Manila',

    'zones' => [
        'same_area' => ['label' => 'Same province', 'description' => 'Store and buyer are in the same province (or both in Metro Manila).', 'fee_minor' => 5000],
        'same_island' => ['label' => 'Same island group', 'description' => 'Different provinces in the same island group, e.g. Metro Manila → Laguna.', 'fee_minor' => 8500],
        'near_island' => ['label' => 'Neighbouring island group', 'description' => 'Luzon ↔ Visayas or Visayas ↔ Mindanao.', 'fee_minor' => 12000],
        'far_island' => ['label' => 'Luzon ↔ Mindanao', 'description' => 'The longest routes, between Luzon and Mindanao.', 'fee_minor' => 15000],
        'standard' => ['label' => 'Standard', 'description' => 'Used when an address can\'t be matched to a province.', 'fee_minor' => 6000],
    ],

    'islands' => [
        'luzon' => [
            'Metro Manila', 'NCR', 'National Capital Region',
            'Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province',
            'Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan',
            'Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino',
            'Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales',
            'Batangas', 'Cavite', 'Laguna', 'Quezon', 'Rizal',
            'Marinduque', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Romblon',
            'Albay', 'Camarines Norte', 'Camarines Sur', 'Catanduanes', 'Masbate', 'Sorsogon',
        ],
        'visayas' => [
            'Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental',
            'Bohol', 'Cebu', 'Negros Oriental', 'Siquijor',
            'Biliran', 'Eastern Samar', 'Leyte', 'Northern Samar', 'Samar', 'Western Samar', 'Southern Leyte',
        ],
        'mindanao' => [
            'Zamboanga del Norte', 'Zamboanga del Sur', 'Zamboanga Sibugay', 'City of Isabela',
            'Bukidnon', 'Camiguin', 'Lanao del Norte', 'Misamis Occidental', 'Misamis Oriental',
            'Davao de Oro', 'Compostela Valley', 'Davao del Norte', 'Davao del Sur', 'Davao Occidental', 'Davao Oriental',
            'Cotabato', 'North Cotabato', 'Sarangani', 'South Cotabato', 'Sultan Kudarat',
            'Agusan del Norte', 'Agusan del Sur', 'Dinagat Islands', 'Surigao del Norte', 'Surigao del Sur',
            'Basilan', 'Lanao del Sur', 'Maguindanao', 'Maguindanao del Norte', 'Maguindanao del Sur', 'Sulu', 'Tawi-Tawi',
        ],
    ],
];
