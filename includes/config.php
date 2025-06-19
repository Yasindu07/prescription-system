<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prescription_db');

// Site configuration
define('SITE_NAME', 'Prescription System');
define('BASE_URL', 'http://localhost/prescription-system/');

// Use absolute path for uploads
define('UPLOAD_DIR', realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR);