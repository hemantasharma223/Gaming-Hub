<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Get stats for dashboard
$stats = [];
$queries = [
    'total_products' => "SELECT COUNT(*) FROM products",
    'total_categories' => "SELECT COUNT(*) FROM main_categories",
    'total_subcategories' => "SELECT COUNT(*) FROM subcategories",
    'total_users' => "SELECT COUNT(*) FROM users WHERE is_active = TRUE",
    'total_orders' => "SELECT COUNT(*) FROM orders",
    'pending_orders' => "SELECT COUNT(*) FROM orders WHERE status = 'pending'",
    'today_orders' => "SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()",
    'revenue' => "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'delivered'"
];

foreach ($queries as $key => $sql) {
    $stmt = executeQuery($sql);
    $stats[$key] = $stmt->fetchColumn();
}

// Get recent orders
$recentOrdersSql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, u.email 
                    FROM orders o
                    JOIN users u ON o.user_id = u.user_id
                    ORDER BY o.order_date DESC LIMIT 5";
$recentOrders = executeQuery($recentOrdersSql)->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Products</h5>
                    <h2><?= $stats['total_products'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="products/manage.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>
                    <h2><?= $stats['total_categories'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="categories/manage.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <h2><?= $stats['total_users'] ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="users/manage.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Revenue</h5>
                    <h2>Rs. <?= number_format($stats['revenue'], 2) ?></h2>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="orders/all.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><a href="orders/view.php?id=<?= $order['order_id'] ?>">#<?= $order['order_id'] ?></a></td>
                                    <td><?= $order['email'] ?></td>
                                    <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                    <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
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
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="categories/manage.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-tags-fill"></i> Manage Categories
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="products/add.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-plus-circle-fill"></i> Add New Product
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="orders/pending.php" class="btn btn-outline-warning w-100">
                                <i class="bi bi-cart-check-fill"></i> Pending Orders
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="users/manage.php" class="btn btn-outline-info w-100">
                                <i class="bi bi-people-fill"></i> Manage Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>