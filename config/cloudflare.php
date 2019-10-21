<?php

return [
    'tkn' => env('CLOUDFLARE_TOKEN'),
    'email' => env('CLOUDFLARE_EMAIL'),
    'z' => env('CLOUDFLARE_URL'),
    'content' => env('CLOUDFLARE_IP'),
    'ttl' => '1',
    'service_mode' => '1',
    'url' => env('CLOUDFLARE_URL')
];
