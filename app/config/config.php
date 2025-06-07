<?php
// Cargar variables de entorno
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Configuraciones
return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Sistema de Internos',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'timezone' => $_ENV['TIMEZONE'] ?? 'America/Argentina/Buenos_Aires'
    ],
    'api' => [
        'url' => $_ENV['API_URL'] ?? 'https://ejemplo.local/api/default.php',
        'timeout' => (int)($_ENV['API_TIMEOUT'] ?? 5)
    ],
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'user' => $_ENV['DB_USER'] ?? 'usuario',
        'pass' => $_ENV['DB_PASS'] ?? 'password',
        'name' => $_ENV['DB_NAME'] ?? 'database',
        'timeout' => (int)($_ENV['DB_TIMEOUT'] ?? 3)
    ],
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'error'
    ]
];
