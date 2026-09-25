<?php

declare(strict_types=1);

return [
    // HMAC secret key untuk hashing NISN secara deterministik dan aman
    'hmac_secret' => 'cc08dec857efaa6e8b0d41b7d1b887c8585c679a54575fc22803d0489234aa3d',
    
    // CSRF Configuration
    'csrf_token_name' => '_csrf_token',
    
    // Sesi Configuration
    'session_name' => 'EVOTING_SESSID',
    'session_lifetime' => 7200, // 2 jam
];
