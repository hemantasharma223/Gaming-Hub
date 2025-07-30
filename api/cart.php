<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Action variables
$action = $_POST['action'] ?? '';
$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$userId = (int) ($_SESSION['user_id'] ?? 0);

// Helper function to get session cart data
function getSessionCartData()
{
    if (!isset($_SESSION['sessioncart']) || empty($_SESSION['sessioncart'])) {
        return ['success' => true, 'items' => [], 'total' => 0];
    }

    $productIds = array_keys($_SESSION['sessioncart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $products = executeQuery("SELECT product_id, name, price, discount_price, image FROM products WHERE product_id IN ($placeholders)", $productIds)->fetchAll(PDO::FETCH_ASSOC);

    $cartItems = [];
    $total = 0;

    foreach ($products as $product) {
        $pid = $product['product_id'];
        $qty = $_SESSION['sessioncart'][$pid] ?? 0;

        if ($qty > 0) {
            $price = $product['discount_price'] ? $product['discount_price'] : $product['price'];
            $subtotal = $price * $qty;

            $cartItems[] = [
                'product_id' => $pid,
                'name' => $product['name'],
                'price' => floatval($product['price']),
                'discount_price' => $product['discount_price'] ? floatval($product['discount_price']) : null,
                'image' => $product['image'],
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];

            $total += $subtotal;
        }
    }

    return ['success' => true, 'items' => $cartItems, 'total' => $total];
}

// Check login status
if ($action === 'checklogin') {
    echo json_encode(['logged_in' => isLoggedIn()]);
    exit();
}

// Session cart operations
if ($action === 'removeSessionCart') {
    if (isset($_SESSION['sessioncart'][$productId])) {
        unset($_SESSION['sessioncart'][$productId]);
        $result = getSessionCartData();
        $result['action'] = 'sessionCartUpdated';
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found in session cart.']);
    }
    exit();
}

if ($action === 'addSessionCart') {
    if (!isset($_SESSION['sessioncart'])) {
        $_SESSION['sessioncart'] = [];
    }

    if (isset($_SESSION['sessioncart'][$productId])) {
        $_SESSION['sessioncart'][$productId] += $quantity;
    } else {
        $_SESSION['sessioncart'][$productId] = $quantity;
    }

    $result = getSessionCartData();
    $result['cart_count'] = count($_SESSION['sessioncart']);
    echo json_encode($result);
    exit();
}

if ($action === 'updateSessionCart') {
    if (!isset($_SESSION['sessioncart'][$productId])) {
        echo json_encode(['success' => false, 'message' => 'Product not in cart.']);
        exit();
    }

    $_SESSION['sessioncart'][$productId] = $quantity;

    $result = getSessionCartData();
    $result['action'] = 'getSessionCart';
    echo json_encode($result);
    exit();
}

if ($action === 'getSessionCart') {
    echo json_encode(getSessionCartData());
    exit();
}

// Database cart operations (for logged-in users)
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your cart.']);
    exit();
}

switch ($action) {
    case 'add':
        // Validate product exists and get stock
        $product = executeQuery("SELECT stock FROM products WHERE product_id = ? AND is_active = TRUE", [$productId])->fetch();
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit();
        }

        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
            exit();
        }

        // Check if item already exists in cart
        $cartItem = executeQuery("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId])->fetch();

        if ($cartItem) {
            $newQuantity = $cartItem['quantity'] + $quantity;
            if ($newQuantity > $product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Cannot add more than available stock.']);
                exit();
            }

            executeQuery(
                "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
                [$newQuantity, $userId, $productId]
            );
        } else {
            executeQuery(
                "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)",
                [$userId, $productId, $quantity]
            );
        }

        $cartCount = executeQuery("SELECT COUNT(*) FROM cart WHERE user_id = ?", [$userId])->fetchColumn();
        echo json_encode(['success' => true, 'cart_count' => $cartCount]);
        break;

    case 'update':
        // Check if product exists and get stock
        $product = executeQuery("SELECT stock FROM products WHERE product_id = ?", [$productId])->fetch();
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit();
        }

        if ($quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
            exit();
        }

        // Check if item exists in cart
        $cartItem = executeQuery("SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId])->fetch();
        if (!$cartItem) {
            echo json_encode(['success' => false, 'message' => 'Product not found in cart.']);
            exit();
        }

        executeQuery(
            "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
            [$quantity, $userId, $productId]
        );

        echo json_encode(['success' => true, 'action' => 'get']);
        break;

    case 'remove':
        $result = executeQuery("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId]);

        if ($result->rowCount() > 0) {
            echo json_encode(['success' => true, 'action' => 'removeSuccess']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found in cart.']);
        }
        break;

    case 'get':
        $cartItems = executeQuery("SELECT c.*, p.name, p.price, p.discount_price, p.image 
                          FROM cart c
                          JOIN products p ON c.product_id = p.product_id
                          WHERE c.user_id = ?", [$userId])->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;
        foreach ($cartItems as &$item) {
            $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
            $item['price'] = floatval($item['price']);
            $item['discount_price'] = $item['discount_price'] ? floatval($item['discount_price']) : null;
            $item['subtotal'] = $price * $item['quantity'];
            $total += $item['subtotal'];
        }

        echo json_encode(['success' => true, 'items' => $cartItems, 'total' => $total]);
        break;

    case 'get_count':
        $count = executeQuery("SELECT COUNT(*) FROM cart WHERE user_id = ?", [$userId])->fetchColumn();
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>