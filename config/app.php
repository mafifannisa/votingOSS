<?php

declare(strict_types=1);

return [
    'name' => 'E-Voting OSIS',
    'version' => '1.0.0',
    'env' => 'development',
    'timezone' => 'Asia/Jakarta',
    'debug' => true,
    'base_url' => rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'),
];
