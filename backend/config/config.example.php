<?php
/**
 * Haustor.ba - Base Configuration Template
 *
 * Copy this file to `backend/config/config.php` and fill in your local
 * or production environment credentials. `config.php` is git-ignored.
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'haustor_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

define('JWT_SECRET', 'replace_with_a_strong_random_jwt_secret_key');

define('EXTERNAL_API_KEY', 'replace_with_your_external_api_key');

return [
    'DB_HOST'          => DB_HOST,
    'DB_PORT'          => DB_PORT,
    'DB_NAME'          => DB_NAME,
    'DB_USER'          => DB_USER,
    'DB_PASSWORD'      => DB_PASSWORD,
    'JWT_SECRET'       => JWT_SECRET,
    'EXTERNAL_API_KEY' => EXTERNAL_API_KEY,
];
