<?php

return [
    'office_start' => env('OFFICE_START_TIME', '09:00'),
    'office_end' => env('OFFICE_END_TIME', '18:30'),
    'grace_minutes' => env('GRACE_MINUTES', 15),
    'minimum_work_minutes' => env('MINIMUM_WORK_MINUTES', 240),
];
