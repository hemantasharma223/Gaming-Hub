<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {

    case 'toggle_user':
        $userId = (int) $_POST['user_id'];
        $newStatus = ($_POST['status'] == 1) ? 1 : 0;

        $result = executeQuery("UPDATE users SET is_active = ? WHERE user_id = ?", [$newStatus, $userId]);

        if ($result && $result->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed or no change.']);
        }
        break;

    case 'delete_product':
        $productId = (int) $_POST['product_id'];
        $product = executeQuery("SELECT image FROM products WHERE product_id = ?", [$productId])->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit();
        }

        if ($product['image'] && file_exists(__DIR__ . '/../assets/uploads/products/' . $product['image'])) {
            unlink(__DIR__ . '/../assets/uploads/products/' . $product['image']);
        }

        executeQuery("DELETE FROM products WHERE product_id = ?", [$productId]);
        echo json_encode(['success' => true]);
        break;

    case 'process_order':
        $orderId = (int) $_POST['order_id'];
        $order = executeQuery("SELECT status FROM orders WHERE order_id = ?", [$orderId])->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit();
        }

        if ($order['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Order is already processed.']);
            exit();
        }

        executeQuery("UPDATE orders SET status = 'processing' WHERE order_id = ?", [$orderId]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_category':
        $categoryId = (int) $_POST['category_id'];
        $category = executeQuery("SELECT image FROM main_categories WHERE category_id = ?", [$categoryId])->fetch();

        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category not found.']);
            exit();
        }

        if ($category['image'] && file_exists(__DIR__ . '/../assets/uploads/categories/' . $category['image'])) {
            unlink(__DIR__ . '/../assets/uploads/categories/' . $category['image']);
        }

        executeQuery("DELETE FROM main_categories WHERE category_id = ?", [$categoryId]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_subcategory':
        $subcategoryId = (int) $_POST['subcategory_id'];
        $subcategory = executeQuery("SELECT * FROM subcategories WHERE subcategory_id = ?", [$subcategoryId])->fetch();

        if (!$subcategory) {
            echo json_encode(['success' => false, 'message' => 'Subcategory not found.']);
            exit();
        }

        executeQuery("DELETE FROM subcategories WHERE subcategory_id = ?", [$subcategoryId]);
        echo json_encode(['success' => true]);
        break;

    case 'update_order_status':
        $orderId = (int) $_POST['order_id'];
        $order = executeQuery("SELECT status FROM orders WHERE order_id = ?", [$orderId])->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found.']);
            exit();
        }

        $statusMap = [
            'pending' => 'processing',
            'processing' => 'shipped',
            'shipped' => 'delivered'
        ];

        $currentStatus = $order['status'];

        if (!isset($statusMap[$currentStatus])) {
            echo json_encode(['success' => false, 'message' => 'Order cannot be advanced further.']);
            exit();
        }

        $newStatus = $statusMap[$currentStatus];

        executeQuery("UPDATE orders SET status = ?, response_date = NOW() WHERE order_id = ?", [$newStatus, $orderId]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
