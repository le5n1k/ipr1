<?php

require_once 'config.php';


function htmlEscape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function handleFileUpload($file, &$errors) {
    $uploadDir = 'uploads/';
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
    
    // Проверяем, был ли загружен файл
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // Файл не обязателен
    }
    
    // Проверяем ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'Размер файла превышает максимально допустимый';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'Файл был загружен частично';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errors[] = 'Отсутствует временная папка';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errors[] = 'Ошибка записи файла на диск';
                break;
            default:
                $errors[] = 'Неизвестная ошибка при загрузке файла';
        }
        return null;
    }
    
    // Проверяем размер файла
    if ($file['size'] > $maxFileSize) {
        $errors[] = 'Размер файла превышает 10MB';
        return null;
    }
    
    // Получаем расширение файла
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Проверяем тип файла
    if (!in_array($fileExtension, $allowedTypes)) {
        $errors[] = 'Недопустимый тип файла. Разрешены: ' . implode(', ', $allowedTypes);
        return null;
    }
    
    // Создаем уникальное имя файла
    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $filePath = $uploadDir . $fileName;
    
    // Перемещаем файл в целевую директорию
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        $errors[] = 'Ошибка при сохранении файла';
        return null;
    }
    
    return $filePath;
}


function displayResult($success, $message, $errors = []) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Результат обработки заказа</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow">
                        <div class="card-header <?php echo $success ? 'bg-success' : 'bg-danger'; ?> text-white">
                            <h2 class="card-title mb-0 text-center">
                                <i class="fas <?php echo $success ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-2"></i>
                                <?php echo $success ? 'Заказ успешно оформлен!' : 'Ошибка обработки заказа'; ?>
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                <strong><?php echo htmlEscape($message); ?></strong>
                            </div>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-warning" role="alert">
                                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Обнаружены следующие ошибки:</h5>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlEscape($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center mt-4">
                                <a href="form.html" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Вернуться к форме
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Location: form.html');
        exit;
    }
    displayResult(false, 'Неверный метод запроса. Используйте POST.');
    exit;
}

$errors = [];


$document_name = isset($_POST['document_name']) ? trim($_POST['document_name']) : '';
$print_format = isset($_POST['print_format']) ? trim($_POST['print_format']) : '';
$copies = isset($_POST['copies']) ? (int)$_POST['copies'] : 0;
$pickup_date = isset($_POST['pickup_date']) ? trim($_POST['pickup_date']) : '';

// Обработка загруженного файла
$file_path = handleFileUpload($_FILES['document_file'] ?? null, $errors);

if (empty($document_name)) {
    $errors[] = 'Название документа не может быть пустым';
} elseif (strlen($document_name) > 255) {
    $errors[] = 'Название документа не может превышать 255 символов';
} elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ0-9\s\-_.,()]+$/u', $document_name)) {
    $errors[] = 'Название документа содержит недопустимые символы';
}


$allowed_formats = ['A4', 'A3', 'A5', 'Letter', 'Legal'];
if (empty($print_format)) {
    $errors[] = 'Формат печати должен быть выбран';
} elseif (!in_array($print_format, $allowed_formats)) {
    $errors[] = 'Недопустимый формат печати';
}


if ($copies <= 0) {
    $errors[] = 'Количество копий должно быть больше 0';
} elseif ($copies > 1000) {
    $errors[] = 'Количество копий не может превышать 1000';
}

if (empty($pickup_date)) {
    $errors[] = 'Дата получения должна быть указана';
} else {
    $pickup_timestamp = strtotime($pickup_date);
    $today_timestamp = strtotime(date('Y-m-d'));
    
    if ($pickup_timestamp === false) {
        $errors[] = 'Неверный формат даты получения';
    } elseif ($pickup_timestamp < $today_timestamp) {
        $errors[] = 'Дата получения не может быть в прошлом';
    }
}

if (!empty($errors)) {
    displayResult(false, 'Проверьте правильность заполнения формы.', $errors);
    exit;
}

$connection = getDatabaseConnection();
if (!$connection) {
    displayResult(false, 'Ошибка подключения к базе данных. Попробуйте позже.');
    exit;
}

try {
    $stmt = $connection->prepare("INSERT INTO print_orders (document_name, print_format, copies, pickup_date, file_path) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Ошибка подготовки запроса: ' . $connection->error);
    }
    
    $stmt->bind_param("ssiss", $document_name, $print_format, $copies, $pickup_date, $file_path);
    
    if ($stmt->execute()) {
        $order_id = $connection->insert_id;
        displayResult(true, "Ваш заказ успешно принят! Номер заказа: #$order_id");
    } else {
        throw new Exception('Ошибка выполнения запроса: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Ошибка при сохранении заказа: " . $e->getMessage());
    displayResult(false, 'Произошла ошибка при сохранении заказа. Попробуйте позже.');
} finally {
    if ($connection) {
        $connection->close();
    }
}
?>

