<?php

require_once 'config.php';

function htmlEscape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d.m.Y H:i:s', strtotime($datetime));
}


$connection = getDatabaseConnection();
$orders = [];
$error_message = '';

if (!$connection) {
    $error_message = 'Ошибка подключения к базе данных';
} else {
    try {
     
        $query = "SELECT id, document_name, print_format, copies, pickup_date, created_at FROM print_orders ORDER BY created_at DESC";
        $result = $connection->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        } else {
            $error_message = 'Ошибка при получении данных: ' . $connection->error;
        }
    } catch (Exception $e) {
        $error_message = 'Произошла ошибка при загрузке заказов';
        error_log("Ошибка при получении заказов: " . $e->getMessage());
    } finally {
        $connection->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все заказы на печать</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-4">
  
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="mb-0">
                        <i class="fas fa-list me-2"></i>Все заказы на печать
                    </h1>
                    <div>
                        <a href="form.html" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-2"></i>Новый заказ
                        </a>
                        <a href="index.html" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Главная
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>

            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Ошибка:</strong> <?php echo htmlEscape($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders) && empty($error_message)): ?>
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <h4>Заказов пока нет</h4>
                <p class="mb-0">База данных пуста. <a href="form.html" class="alert-link">Создайте первый заказ</a>.</p>
            </div>
        <?php else: ?>
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-primary text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Всего заказов</h5>
                                    <h2 class="mb-0"><?php echo count($orders); ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-cart fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-success text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Всего копий</h5>
                                    <h2 class="mb-0"><?php echo array_sum(array_column($orders, 'copies')); ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-copy fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-info text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Популярный формат</h5>
                                    <h2 class="mb-0">
                                        <?php 
                                        if (!empty($orders)) {
                                            $formats = array_column($orders, 'print_format');
                                            $format_counts = array_count_values($formats);
                                            echo array_search(max($format_counts), $format_counts);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-expand-arrows-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card bg-warning text-white stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Последний заказ</h5>
                                    <h2 class="mb-0" style="font-size: 1.5rem;">
                                        <?php 
                                        if (!empty($orders)) {
                                            echo formatDate($orders[0]['created_at']);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Список всех заказов
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Название документа</th>
                                    <th scope="col">Формат</th>
                                    <th scope="col">Копии</th>
                                    <th scope="col">Дата получения</th>
                                    <th scope="col">Дата создания</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlEscape($order['id']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlEscape($order['document_name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlEscape($order['print_format']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo htmlEscape($order['copies']); ?></span>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?php echo formatDate($order['pickup_date']); ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo formatDateTime($order['created_at']); ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col text-center">
                <div class="btn-group" role="group">
                    <a href="form.html" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Добавить заказ
                    </a>
                    <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt me-2"></i>Обновить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


