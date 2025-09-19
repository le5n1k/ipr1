<?php
/**
 * Конфигурационный файл для подключения к базе данных
 * Содержит настройки подключения к MySQL
 */

// Настройки подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '1111');  // Укажите ваш пароль от MySQL
define('DB_NAME', 'user_form_db_rakhmanko');  // Замените "фамилия" на вашу фамилию
define('DB_CHARSET', 'utf8mb4');

/**
 * Функция для создания подключения к базе данных
 * @return mysqli|false Объект подключения или false при ошибке
 */
function getDatabaseConnection() {
    $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Проверка подключения
    if ($connection->connect_error) {
        error_log("Ошибка подключения к БД: " . $connection->connect_error);
        return false;
    }
    
    // Установка кодировки
    $connection->set_charset(DB_CHARSET);
    
    return $connection;
}
?>




