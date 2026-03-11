<?php

use Laravel\Sanctum\Sanctum;

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost')),

    'guard' => ['web'],

    // Durata access token in minuti (null = nessuna scadenza)
    'expiration' => env('SANCTUM_ACCESS_TOKEN_EXPIRATION', 60),

    // Aggiungi questa chiave custom per il refresh token
    'refresh_token_expiration' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION', 30), // 30 giorni in minuti
];
