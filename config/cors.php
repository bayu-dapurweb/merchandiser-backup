<?php
return [
    'paths' => ['api/*',],
    'allowed_methods' => ['POST', 'GET', 'DELETE', 'PUT', '*'],
    'allowed_origins' => ['http://localhost:8080', 'http://localhost:8081', 'https://salutaria.pelangisentralkreasi.co.id', '*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['X-Custom-Header', 'Upgrade-Insecure-Requests', '*'],
    'exposed_headers' => false,
    'max_age' => false,
    'supports_credentials' => false,
];
