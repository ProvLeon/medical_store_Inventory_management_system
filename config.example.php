<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'final_project');

// Table names
define('DB_TABLE_USERS', 'users');
define('DB_TABLE_PERSON', 'person');
define('DB_TABLE_EMPLOYEE', 'employee');
define('DB_TABLE_MEDICINE', 'medicine');
define('DB_TABLE_TRANSACTION', 'transaction');
define('DB_TABLE_TRANSACTION_ITEMS', 'transaction_items');
define('DB_TABLE_TXN_PERSON', 'txn_person');

// Other configuration settings
define('SITE_NAME', 'Medical Store Management System');
define('ADMIN_EMAIL', 'admin@example.com');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
// ini_set('session.cookie_httponly', 1);
// ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 1);

// Timezone
date_default_timezone_set('UTC');
