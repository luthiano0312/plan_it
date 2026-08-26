<?php

return [
    'priority' => [
        'due_weight' => env('PLANIT_DUE_WEIGHT', 3.0),
        'ease_weight' => env('PLANIT_EASE_WEIGHT', 1.0),
        'urgency_max' => env('PLANIT_URGENCY_MAX', 10.0),
        'urgency_horizon_days' => env('PLANIT_URGENCY_HORIZON_DAYS', 14),
        'urgency_no_due' => env('PLANIT_URGENCY_NO_DUE', 1.0),
    ],
    'shortlist_size' => env('PLANIT_SHORTLIST_SIZE', 5),
];
