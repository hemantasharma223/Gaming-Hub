<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to access orders.']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_orders':
        $orders = executeQuery("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC", [$userId])
                  ->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'orders' => $orders]);
        break;
        
    case 'get_order_details':
        $orderId = (int)$_GET['order_id'];
        
        // Verify order belongs to user
        $order = executeQuery("SELECT * FROM orders WHERE order_id = ? AND user_id = ?", [$orderId, $userId])
                 ->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit();
        }
        
        // Get order items
        $items = executeQuery("SELECT oi.*, p.name, p.image 
                              FROM order_items oi
                              JOIN products p ON oi.product_id = p.product_id
                              WHERE oi.order_id = ?", [$orderId])
                ->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>