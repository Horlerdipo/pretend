<?php

// config for Horlerdipo/Pretend
return [

    // @phpstan-ignore-next-line
    'impersonation_token_length' => env('PRETEND_IMPERSONATION_TOKEN_LENGTH', 32),

    // @phpstan-ignore-next-line
    'allow_events_dispatching' => env('PRETEND_ALLOW_EVENTS_DISPATCHING', true),

    // @phpstan-ignore-next-line
    'impersonation_token_ttl' => env('PRETEND_IMPERSONATION_TOKEN_TTL', 10), // IN MINUTES

    // @phpstan-ignore-next-line
    'auth_token_prefix' => env('PRETEND_AUTH_TOKEN_PREFIX', 'impersonation-token'),

    'impersonation_storage' => \Horlerdipo\Pretend\Storage\DatabaseStorage::class,
];
