<?php
// ============================================================
// Database Configuration
// ============================================================

// --- XAMPP (Local / Active) ---
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'concert_ticketing');

// --- AWS RDS (Uncomment for production) ---
// define('DB_HOST', 'your-rds-endpoint.amazonaws.com');
// define('DB_PORT', '3306');
// define('DB_USER', 'admin');
// define('DB_PASS', 'YourSecurePassword');
// define('DB_NAME', 'concert_ticketing');

define('BASE_URL', 'http://localhost/konser-ticketing');
define('UPLOAD_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
