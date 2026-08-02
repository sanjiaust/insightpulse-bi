<?php
// Database connection settings.
// Default XAMPP MySQL credentials: user "root", empty password.
// Change these if your local setup differs.

define('APP_NAME', 'InsightPulse BI');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'insightpulse_bi');

function get_db_connection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error .
                          '. Make sure MySQL is running in XAMPP and you have imported sql/schema.sql.'
        ]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
