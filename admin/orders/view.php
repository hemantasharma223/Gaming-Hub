<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: /gaming_hub/admin/index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: pending.php");
    exit();
}

$orderId = (int)$_GET['id'];

// Get order details
$order = executeQuery("SELECT o.*, u.email, u.full_name, u.phone 
                      FROM orders o
                      JOIN users u ON o.user_id = u.user_id
                      WHERE o.order_id = ?", [$orderId])
         ->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: pending.php");
    exit();
}

// Get order items
$orderItems = executeQuery("SELECT oi.*, p.name, p.image 
                           FROM order_items oi
                           JOIN products p ON oi.product_id = p.product_id
                           WHERE oi.order_id = ?", [$orderId])
              ->fetchAll(PDO::FETCH_ASSOC);

// Process response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response'])) {
    $response = sanitize($_POST['response']);
    $status = sanitize($_POST['status']);
    
    $sql = "UPDATE orders SET status = ?, admin_response = ?, response_date = NOW() WHERE order_id = ?";
    executeQuery($sql, [$status, $response, $orderId]);
    
    $_SESSION['success_message'] = "Order updated successfully!";
    header("Location: view.php?id=$orderId");
    exit();
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Order #<?= $order['order_id'] ?></h6>
                        <div>
                            <a href="<?= $order['status'] === 'pending' ? 'pending.php' : 'all.php' ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success mx-4">
                            <p class="mb-0"><?= $_SESSION['success_message'] ?></p>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <div class="row p-4">
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Order Items</h6>
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
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Update Order Status</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label for="status" class="form-control-label">Status</label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="form-group mt-3">
                                            <label for="response" class="form-control-label">Admin Response</label>
                                            <textarea class="form-control" id="response" name="response" rows="3"><?= htmlspecialchars($order['admin_response'] ?? '') ?></textarea>
                                            <small class="text-muted">This will be visible to the customer.</small>
                                        </div>
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary">Update Order</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Order Information</h6>
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
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Name:</th>
                                            <td><?= $order['full_name'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?= $order['email'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Phone:</th>
                                            <td><?= $order['phone'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Shipping Information</h6>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>