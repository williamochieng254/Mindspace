<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

/**
 * Returns an active PDO connection.
 */
function establish_db_connection(): PDO {
    try {
        return db();
    } catch (Throwable $e) {
        throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
    }
}

// Quick connection check when this file is run directly.
if (basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'db_connect.php') {
    try {
        establish_db_connection();
        echo 'Database connected successfully.';
    } catch (Throwable $e) {
        http_response_code(500);
        echo $e->getMessage();
    }
}
