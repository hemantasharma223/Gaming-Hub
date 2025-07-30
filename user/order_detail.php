<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: /user/auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: /user/myorders.php");
    exit();
}

$orderId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

// Get order details
$order = executeQuery("SELECT * FROM orders WHERE order_id = ? AND user_id = ?", [$orderId, $userId])
         ->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: /user/myorders.php");
    exit();
}

// Get order items
$orderItems = executeQuery("SELECT oi.*, p.name, p.image 
                           FROM order_items oi
                           JOIN products p ON oi.product_id = p.product_id
                           WHERE oi.order_id = ?", [$orderId])
              ->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order #<?= $order['order_id'] ?></h1>
        <a href="myorders.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="/gaming_hub/assets/uploads/products/<?= $item['image'] ?>" 
                                                 alt="<?= $item['name'] ?>" class="img-thumbnail me-3" style="width: 60px;">
                                            <div>
                                                <h6 class="mb-0"><?= $item['name'] ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rs. <?= number_format($item['unit_price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>Rs. <?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>Rs. <?= number_format($order['total_amount'], 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Order Date:</th>
                            <td><?= date('M d, Y h:i A', strtotime($order['order_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge 
                                    <?= $order['status'] == 'pending' ? 'bg-warning' : 
                                       ($order['status'] == 'processing' ? 'bg-info' : 
                                       ($order['status'] == 'shipped' ? 'bg-primary' : 
                                       ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger'))) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Method:</th>
                            <td><?= $order['payment_method'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <address>
                        <strong>Contact Number:</strong><br>
                        <?= $order['contact_number'] ?><br><br>
                        <strong>Shipping Address:</strong><br>
                        <?= nl2br($order['shipping_address']) ?>
                    </address>
                </div>
            </div>
            
            <?php if ($order['admin_response']): ?>
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Admin Response</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br($order['admin_response']) ?></p>
                    <small class="text-muted">
                        Updated on <?= date('M d, Y h:i A', strtotime($order['response_date'])) ?>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>