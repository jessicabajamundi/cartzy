<?php

return [
    // Operational limits in kg and cm. Confirm against the actual fleet before enabling bulky service.
    'vehicles' => [
        'motorcycle' => ['kg' => 20, 'cm' => [60, 45, 45]],
        'ebike' => ['kg' => 50, 'cm' => [80, 60, 60]],
        'tricycle' => ['kg' => 150, 'cm' => [120, 100, 100]],
        'car' => ['kg' => 150, 'cm' => [100, 80, 60]],
        'van' => ['kg' => 1000, 'cm' => [260, 150, 150]],
        'truck' => ['kg' => 5000, 'cm' => [600, 240, 240]],
        'cargo' => ['kg' => 10000, 'cm' => [1200, 260, 240]],
    ],
    'bulky_vehicles' => ['van', 'truck', 'cargo'],
];
