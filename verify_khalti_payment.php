<?php
require_once __DIR__ . '../config/database.php'; // PDO config
require_once __DIR__ . '../includes/functions.php'; // your helper functions
// Get cart items
$cartItems = executeQuery("SELECT c.*, p.name, p.price, p.discount_price 
                          FROM cart c
                          JOIN products p ON c.product_id = p.product_id
                          WHERE c.user_id = ?", [$_SESSION['user_id']])->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    header("Location: /gaming_hub/user/cart.php");
    exit();
}

$pidx = $_GET['pidx'] ?? null;

if ($pidx) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => [
            'Authorization: key b2d09c43309f41feb8db370e76c37558',
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    if ($response) {
        $data = json_decode($response, true);
        echo "<script>console.log(" . json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ");</script>";

        switch ($data['status']) {
            case 'Completed':
               // Begin transaction
            $pdo->beginTransaction();
            
            // Create order
            $orderSql = "INSERT INTO orders (user_id, shipping_address, contact_number, payment_method, total_amount)
                         VALUES (?, ?, ?, ?, ?)";
            executeQuery($orderSql, [ $_SESSION['user_id'], $_SESSION['shipping_address'], $_SESSION['contact_number'], $_SESSION['payment_method'], $_SESSION['total']] ); 
            $orderId = $pdo->lastInsertId();
            $_SESSION['order_id'] = $orderId;
            
            // Add order items
            foreach ($cartItems as $item) {
                $price = $item['discount_price'] ? $item['discount_price'] : $item['price'];
                $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                            VALUES (?, ?, ?, ?)";
                executeQuery($itemSql, [$orderId, $item['product_id'], $item['quantity'], $price]);
                
                // Update product stock
                executeQuery("UPDATE products SET stock = stock - ? WHERE product_id = ?", 
                            [$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            executeQuery("DELETE FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
            
            // Commit transaction
            $pdo->commit();

                $_SESSION['transaction_msg'] = '<script>
                    Swal.fire({
                        icon: "success",
                        title: "Transaction successful.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';

                echo "<script>
                    alert('Payment completed: " . $data['transaction_id'] . "');
                    window.location.href = '/gaming_hub/user/order_detail.php?id=" . $_SESSION['order_id'] . "';
                </script>";
                exit();

            case 'Expired':
            case 'User canceled':
                $_SESSION['transaction_msg'] = '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Transaction failed.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';

                echo "<script>
                    alert('Payment canceled');
                    window.location.href = '/gaming_hub/user/cart.php';
                </script>";
                exit();

            default:
                $_SESSION['transaction_msg'] = '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Transaction failed.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                </script>';

                header("Location: /gaming_hub/user/cart.php");
                exit();
        }
    } else {
        // If no response
        echo "<script>alert('Failed to communicate with Khalti.'); window.location.href='/gaming_hub/user/cart.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid Payment Identifier.'); window.location.href='/gaming_hub/user/cart.php';</script>";
    exit();
}
