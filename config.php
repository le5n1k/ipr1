<?php

define('DB_HOST', '127.0.0.1');
define('DB_USERNAME', 'phpuser');
define('DB_PASSWORD', 'password123');
define('DB_NAME', 'user_form_db_rakhmanko');
define('DB_CHARSET', 'utf8mb4');

function getDatabaseConnection() {
    $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($connection->connect_error) {
        error_log("Ошибка подключения к БД: " . $connection->connect_error);
        return false;
    }
    $connection->set_charset(DB_CHARSET);
    
    return $connection;
}
?>







