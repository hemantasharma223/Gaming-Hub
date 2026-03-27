<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $actionType = isset($_POST['action_type']) ? sanitize($_POST['action_type']) : 'view';
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($productId > 0 && in_array($actionType, ['view', 'cart', 'purchase'])) {
        $sql = "INSERT INTO user_activity (user_id, product_id, action_type) VALUES (?, ?, ?)";
        executeQuery($sql, [$userId, $productId, $actionType]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
