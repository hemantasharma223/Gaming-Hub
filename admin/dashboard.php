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
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-primary"><i class="bi bi-controller"></i></div>
                <div class="stat-value"><?= $stats['total_products'] ?></div>
                <div class="stat-label">Total Products</div>
                <div class="mt-3">
                    <a class="btn btn-outline-primary btn-sm rounded-pill px-3" href="products/manage.php">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-success"><i class="bi bi-tags"></i></div>
                <div class="stat-value"><?= $stats['total_categories'] ?></div>
                <div class="stat-label">Categories</div>
                <div class="mt-3">
                    <a class="btn btn-outline-success btn-sm rounded-pill px-3" href="categories/manage.php">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-warning"><i class="bi bi-people"></i></div>
                <div class="stat-value"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Active Users</div>
                <div class="mt-3">
                    <a class="btn btn-outline-warning btn-sm rounded-pill px-3" href="users/manage.php">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-info">
                <div class="stat-icon text-info"><i class="bi bi-cash"></i></div>
                <div class="stat-value">Rs. <?= number_format($stats['revenue'], 2) ?></div>
                <div class="stat-label">Total Revenue</div>
                <div class="mt-3">
                    <a class="btn btn-outline-info btn-sm rounded-pill px-3" href="orders/all.php">View Details</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-clock-history me-2"></i>Recent Orders</h5>
                    <a href="orders/all.php" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
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
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-info fw-bold"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="categories/manage.php" class="btn btn-outline-primary text-start py-3 fs-5 rounded-3">
                            <i class="bi bi-tags ms-2 me-3 fs-4"></i> Manage Categories
                        </a>
                        <a href="products/add.php" class="btn btn-outline-success text-start py-3 fs-5 rounded-3">
                            <i class="bi bi-plus-circle ms-2 me-3 fs-4"></i> Add New Product
                        </a>
                        <a href="orders/pending.php" class="btn btn-outline-warning text-start py-3 fs-5 rounded-3">
                            <i class="bi bi-cart-check ms-2 me-3 fs-4"></i> Pending Orders
                        </a>
                        <a href="users/manage.php" class="btn btn-outline-info text-start py-3 fs-5 rounded-3">
                            <i class="bi bi-people ms-2 me-3 fs-4"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>