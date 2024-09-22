<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'medical_store_db');

// Other configuration settings
define('SITE_NAME', 'Medical Store Management System');
define('ADMIN_EMAIL', 'admin@example.com');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Timezone
date_default_timezone_set('UTC');
