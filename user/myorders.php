<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: auth/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get all orders for the user
$orders = executeQuery("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC", [$userId])
          ->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">My Orders</h1>
    
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            You haven't placed any orders yet. <a href="/gaming_hub/" class="alert-link">Start shopping</a>.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['order_id'] ?></td>
                        <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                        <td>
                            <?php 
                            $itemCount = executeQuery("SELECT COUNT(*) FROM order_items WHERE order_id = ?", 
                                                     [$order['order_id']])->fetchColumn();
                            echo $itemCount;
                            ?>
                        </td>
                        <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                        <td>
                            <span class="badge 
                                <?= $order['status'] == 'pending' ? 'bg-warning' : 
                                   ($order['status'] == 'processing' ? 'bg-info' : 
                                   ($order['status'] == 'shipped' ? 'bg-primary' : 
                                   ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger'))) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                            <?php if ($order['status'] == 'shipped' || $order['status'] == 'delivered'): ?>
                                <?php if ($order['admin_response']): ?>
                                    <button class="btn btn-sm btn-link" data-bs-toggle="popover" 
                                            title="Admin Response" data-bs-content="<?= htmlspecialchars($order['admin_response']) ?>">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-primary">
                                View Details
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('[data-bs-toggle="popover"]').popover({
        trigger: 'hover',
        placement: 'top'
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>