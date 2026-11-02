<?php
/**
 * Haustor.ba - Base Configuration Template
 *
 * Copy this file to `backend/config/config.php` and fill in your local
 * or production environment credentials. `config.php` is git-ignored.
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', 3306);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'haustor_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', DB_PASS);
}

if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'replace_with_a_strong_random_jwt_secret_key');
}

if (!defined('EXTERNAL_API_KEY')) {
    define('EXTERNAL_API_KEY', 'replace_with_your_external_api_key');
}

return [
    'DB_HOST'          => DB_HOST,
    'DB_PORT'          => DB_PORT,
    'DB_NAME'          => DB_NAME,
    'DB_USER'          => DB_USER,
    'DB_PASS'          => DB_PASS,
    'DB_PASSWORD'      => DB_PASSWORD,
    'JWT_SECRET'       => JWT_SECRET,
    'EXTERNAL_API_KEY' => EXTERNAL_API_KEY,
];
