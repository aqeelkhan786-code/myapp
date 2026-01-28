<?php

return [

    'use_pdf_templates' => env('RENTAL_USE_PDF_TEMPLATES', true),

    'templates' => [
        'en' => 'rental-templates/rental-agreement-en.pdf',
        'de' => 'rental-templates/rental-agreement-de.pdf',
    ],

    'overlay' => [

        /*
        |--------------------------------------------------------------------------
        | ENGLISH TEMPLATE
        |--------------------------------------------------------------------------
        */
        'en' => [

            // ---------- PAGE 1 ----------
            'date' => ['x' => 150, 'y' => 30, 'size' => 10, 'page' => 1],

            'tenant_name' => ['x' => 67, 'y' => 97, 'size' => 10, 'page' => 1],
            'tenant_address' => ['x' => 67, 'y' => 105, 'size' => 9, 'page' => 1],
            'tenant_city' => ['x' => 67, 'y' => 110, 'size' => 9, 'page' => 1],
            'tenant_postal_code' => ['x' => 67, 'y' => 115, 'size' => 9, 'page' => 1],
            'tenant_phone' => ['x' => 67, 'y' => 120, 'size' => 9, 'page' => 1],
            'tenant_email' => ['x' => 67, 'y' => 125, 'size' => 9, 'page' => 1],

            'room_name' => ['x' => 51, 'y' => 145, 'size' => 10, 'page' => 1],
            'property_address' => ['x' => 103, 'y' => 155, 'size' => 9, 'page' => 1],

            'start_at' => ['x' => 50, 'y' => 225, 'size' => 10, 'page' => 1],

            // ---------- PAGE 2 ----------
            'rent' => ['x' => 40, 'y' => 30, 'size' => 10, 'page' => 2],
            'deposit' => ['x' => 70, 'y' => 95, 'size' => 10, 'page' => 2],

            // ---------- PAGE 3 ----------
            'owner_signature' => ['x' => 25, 'y' => 80, 'w' => 40, 'h' => 20, 'page' => 3],
            'tenant_signature' => ['x' => 105, 'y' => 80, 'w' => 40, 'h' => 20, 'page' => 3],
            'tenant_signed_at' => ['x' => 115, 'y' => 101, 'size' => 9, 'page' => 3],
        ],

        /*
        |--------------------------------------------------------------------------
        | GERMAN TEMPLATE (EXACT COPY OF EN)
        |--------------------------------------------------------------------------
        */
        'de' => [

            // ---------- PAGE 1 ----------
            'date' => ['x' => 150, 'y' => 30, 'size' => 10, 'page' => 1],

            'tenant_name' => ['x' => 67, 'y' => 97, 'size' => 10, 'page' => 1],
            'tenant_address' => ['x' => 67, 'y' => 105, 'size' => 9, 'page' => 1],
            'tenant_city' => ['x' => 67, 'y' => 110, 'size' => 9, 'page' => 1],
            'tenant_postal_code' => ['x' => 67, 'y' => 115, 'size' => 9, 'page' => 1],
            'tenant_phone' => ['x' => 67, 'y' => 120, 'size' => 9, 'page' => 1],
            'tenant_email' => ['x' => 67, 'y' => 125, 'size' => 9, 'page' => 1],

            'room_name' => ['x' => 51, 'y' => 145, 'size' => 10, 'page' => 1],
            'property_address' => ['x' => 103, 'y' => 155, 'size' => 9, 'page' => 1],

            'start_at' => ['x' => 50, 'y' => 225, 'size' => 10, 'page' => 1],

            // ---------- PAGE 2 ----------
            'rent' => ['x' => 40, 'y' => 30, 'size' => 10, 'page' => 2],
            'deposit' => ['x' => 70, 'y' => 95, 'size' => 10, 'page' => 2],

            // ---------- PAGE 3 ----------
            'owner_signature' => ['x' => 25, 'y' => 80, 'w' => 40, 'h' => 20, 'page' => 3],
            'tenant_signature' => ['x' => 105, 'y' => 80, 'w' => 40, 'h' => 20, 'page' => 3],
            'tenant_signed_at' => ['x' => 115, 'y' => 101, 'size' => 9, 'page' => 3],
        ],
    ],
];
