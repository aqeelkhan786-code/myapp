<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking / iCal Timezone
    |--------------------------------------------------------------------------
    |
    | Used for parsing and generating iCal feeds (Airbnb calendar sync).
    | Existing booking flow uses Europe/Berlin, so we default to that for
    | consistency. Override via BOOKING_TIMEZONE in .env.
    |
    */
    'timezone' => env('BOOKING_TIMEZONE', 'Europe/Berlin'),
];

