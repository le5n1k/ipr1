<?php


require_once 'config.php';

echo "<h2>Обновление базы данных</h2>\n";

$connection = getDatabaseConnection();

if (!$connection) {
    echo "<p style='color: red;'>Ошибка подключения к базе данных</p>\n";
    exit;
}

try {

    $checkQuery = "SHOW COLUMNS FROM print_orders LIKE 'file_path'";
    $result = $connection->query($checkQuery);
    
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Поле file_path уже существует в таблице print_orders</p>\n";
    } else {
        $alterQuery = "ALTER TABLE print_orders ADD COLUMN file_path VARCHAR(500) DEFAULT NULL";
        
        if ($connection->query($alterQuery)) {
            echo "<p style='color: green;'>✓ Поле file_path успешно добавлено в таблицу print_orders</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Ошибка при добавлении поля file_path: " . $connection->error . "</p>\n";
        }
    }
    
    echo "<h3>Структура таблицы print_orders:</h3>\n";
    $describeQuery = "DESCRIBE print_orders";
    $result = $connection->query($describeQuery);
    
    if ($result) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>Поле</th><th>Тип</th><th>NULL</th><th>Ключ</th><th>По умолчанию</th><th>Дополнительно</th></tr>\n";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>\n";
} finally {
    $connection->close();
}

echo "<p><a href='form.html'>← Вернуться к форме</a></p>\n";
?>
