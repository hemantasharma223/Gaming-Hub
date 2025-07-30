<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Action variables
$action = $_POST['action'] ?? '';
$productId = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;
$userId = $_SESSION['user_id'] ?? 0;
if ($action === 'checklogin') {
    echo json_encode(['logged_in' => isLoggedIn()]);
    exit();
}

if ($action === 'removeSessionCart') {
    if (isset($_SESSION['sessioncart'][$productId])) {
        unset($_SESSION['sessioncart'][$productId]);
        echo json_encode(['success' => true, 'action' => 'sessionCartUpdated']);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found in session cart.']);
        exit();
    }

    // Fetch updated cart
    $productIds = array_keys($_SESSION['sessioncart'] ?? []);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    if (empty($productIds)) {
        echo json_encode(['success' => true, 'items' => [], 'total' => 0]);
        exit();
    }

    $products = executeQuery("SELECT product_id, name, price, discount_price, image FROM products WHERE product_id IN ($placeholders)", $productIds)->fetchAll(PDO::FETCH_ASSOC);

    $cartItems = [];
    $total = 0;

    foreach ($products as $product) {
        $pid = $product['product_id'];
        $qty = $_SESSION['sessioncart'][$pid] ?? 0;
        $price = $product['discount_price'] ? $product['discount_price'] : $product['price'];
        $subtotal = $price * $qty;

        if ($qty > 0) {
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

    echo json_encode(['success' => true, 'items' => $cartItems, 'total' => $total]);
    exit();
}
if ($action === 'addSessionCart') {
    if (!isset($_SESSION['sessioncart'])) {
        $_SESSION['sessioncart'] = [];
    }

    // Add or update session cart item
    if ($action === 'addSessionCart') {
        if (!isset($_SESSION['sessioncart'])) {
            $_SESSION['sessioncart'] = [];
        }

        if (isset($_SESSION['sessioncart'][$productId])) {
            $_SESSION['sessioncart'][$productId] += $quantity;
            echo "<script>console.log('Session Cart:', JSON.parse(" . json_encode(json_encode($_SESSION['sessioncart'])) . "));</script>";

        } else {
            $_SESSION['sessioncart'][$productId] = $quantity;
        }

        // Now fetch full cart data after update
        $productIds = array_keys($_SESSION['sessioncart']);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $products = executeQuery("SELECT product_id, name, price, discount_price, image FROM products WHERE product_id IN ($placeholders)", $productIds)->fetchAll(PDO::FETCH_ASSOC);

        $cartItems = [];
        $total = 0;

        foreach ($products as $product) {
            $pid = $product['product_id'];
            $qty = $_SESSION['sessioncart'][$pid];
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

        echo json_encode([
            'success' => true,
            'items' => $cartItems,
            'total' => $total,
            'cart_count' => count($_SESSION['sessioncart'])
        ]);
        exit();
    }

}

if ($action === 'updateSessionCart') {
    if (!isset($_SESSION['sessioncart'][$productId])) {
        echo json_encode(['success' => false, 'message' => 'Product not in cart.']);
        exit();
    }

    $_SESSION['sessioncart'][$productId] = max(1, $quantity);

    // Fetch updated cart
    $productIds = array_keys($_SESSION['sessioncart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $products = executeQuery("SELECT product_id, name, price, discount_price, image FROM products WHERE product_id IN ($placeholders)", $productIds)->fetchAll(PDO::FETCH_ASSOC);

    $cartItems = [];
    $total = 0;

    foreach ($products as $product) {
        $pid = $product['product_id'];
        $qty = $_SESSION['sessioncart'][$pid];
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

    echo json_encode([
        'success' => true,
        'action' => 'getSessionCart',
        'items' => $cartItems,
        'total' => $total
    ]);
    exit();
}


if ($action === 'getSessionCart') {
    if (!isset($_SESSION['sessioncart']) || empty($_SESSION['sessioncart'])) {
        echo json_encode(['success' => true, 'items' => [], 'total' => 0]);
        exit();
    }

    $productIds = array_keys($_SESSION['sessioncart']);

    // Prepare placeholders for SQL IN clause (?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    // Fetch product info for all products in session cart
    $products = executeQuery("SELECT product_id, name, price, discount_price, image FROM products WHERE product_id IN ($placeholders)", $productIds)->fetchAll(PDO::FETCH_ASSOC);

    $cartItems = [];
    $total = 0;

    foreach ($products as $product) {
        $pid = $product['product_id'];
        $quantity = $_SESSION['sessioncart'][$pid];
        $price = $product['discount_price'] ? $product['discount_price'] : $product['price'];
        $subtotal = $price * $quantity;

        $cartItems[] = [
            'product_id' => $pid,
            'name' => $product['name'],
            'price' => floatval($product['price']),
            'discount_price' => $product['discount_price'] ? floatval($product['discount_price']) : null,
            'image' => $product['image'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];

        $total += $subtotal;
    }

    echo json_encode(['success' => true, 'items' => $cartItems, 'total' => $total]);
    exit();
}

if (isLoggedIn()) {

    switch ($action) {
        case 'add':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login to manage your cart.']);
                exit();
            } else {

                $product = executeQuery("SELECT stock FROM products WHERE product_id = ? AND is_active = TRUE", [$productId])->fetch();
                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Product not found.']);
                    exit();
                }

                if ($product['stock'] < $quantity) {
                    echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
                    exit();
                }

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
            }

        case 'update':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login to manage your cart.']);
                exit();
            } else {
                $quantity = max(1, $quantity);
                $product = executeQuery("SELECT stock FROM products WHERE product_id = ?", [$productId])->fetch();
                if ($quantity > $product['stock']) {
                    echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
                    exit();
                }

                executeQuery(
                    "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
                    [$quantity, $userId, $productId]
                );

                echo json_encode(['success' => true, 'action' => 'get',]);
                break;
            }
        case 'remove':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login to manage your cart.']);
                exit();
            } else {
                executeQuery("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
                echo json_encode(['success' => true, 'action' => 'removeSuccess']);
                break;
            }

        case 'get':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login to view your cart.']);
                exit();
            } else {
                $cartItems = executeQuery("SELECT c.*, p.name, p.price, p.discount_price, p.image 
                                  FROM cart c
                                  JOIN products p ON c.product_id = p.product_id
                                  WHERE c.user_id = ?", [$userId])->fetchAll(PDO::FETCH_ASSOC);

                $total = 0;
                foreach ($cartItems as &$item) {
                    $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
                    $item['subtotal'] = $price * $item['quantity'];
                    $total += $item['subtotal'];
                }

                echo json_encode(['success' => true, 'items' => $cartItems, 'total' => $total]);
                break;
            }

        case 'get_count':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Please login to manage your cart.']);
                exit();
            } else {
                $count = executeQuery("SELECT COUNT(*) FROM cart WHERE user_id = ?", [$userId])->fetchColumn();
                echo json_encode(['success' => true, 'count' => $count]);
                break;
            }
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
}

?>