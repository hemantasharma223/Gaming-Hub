<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: auth/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user   = executeQuery("SELECT * FROM users WHERE user_id = ?", [$userId])->fetch(PDO::FETCH_ASSOC);
$orders = executeQuery("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5", [$userId])->fetchAll(PDO::FETCH_ASSOC);
$totalOrders = executeQuery("SELECT COUNT(*) FROM orders WHERE user_id = ?", [$userId])->fetchColumn();
$cartCount = getCartCount($userId);
$recentlyViewed = getRecentlyViewed($userId, 4);

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
?>

<div class="container my-5">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body text-center py-4">
                    <div style="width:72px;height:72px;background:linear-gradient(135deg,var(--accent),var(--neon-cyan));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <i class="bi bi-person-fill fs-2 text-white"></i>
                    </div>
                    <h6 class="mb-0"><?= htmlspecialchars($user['full_name'] ?? 'Gamer') ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                </div>
            </div>
            <div class="list-group">
                <a href="dashboard.php" class="list-group-item list-group-item-action active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a href="myorders.php"  class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i>My Orders</a>
                <a href="cart.php"      class="list-group-item list-group-item-action"><i class="bi bi-bag-heart me-2"></i>Cart <span class="badge bg-primary ms-1"><?= $cartCount ?></span></a>
                <a href="auth/logout.php" class="list-group-item list-group-item-action" style="color:var(--danger)"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <h2 class="section-title"><i class="bi bi-speedometer2" style="color:var(--accent-light)"></i> Dashboard</h2>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-box-seam" style="color:var(--accent-light)"></i></div>
                        <div class="stat-value"><?= $totalOrders ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-bag-heart" style="color:var(--neon-cyan)"></i></div>
                        <div class="stat-value"><?= $cartCount ?></div>
                        <div class="stat-label">Items in Cart</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="bi bi-clock-history" style="color:var(--warning)"></i></div>
                        <div class="stat-value"><?= count($recentlyViewed) ?></div>
                        <div class="stat-label">Recently Viewed</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <a href="/gaming_hub/" class="card text-decoration-none" style="background:linear-gradient(135deg,rgba(124,58,237,0.15),rgba(0,212,255,0.05));border-color:rgba(124,58,237,0.3)">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class="bi bi-controller fs-2" style="color:var(--accent-light)"></i>
                            <div><h6 class="mb-0">Browse Store</h6><small class="text-muted">Discover new games</small></div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="myorders.php" class="card text-decoration-none" style="background:linear-gradient(135deg,rgba(0,212,255,0.1),transparent);border-color:rgba(0,212,255,0.25)">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class="bi bi-box-seam fs-2" style="color:var(--neon-cyan)"></i>
                            <div><h6 class="mb-0">My Orders</h6><small class="text-muted">Track your purchases</small></div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Orders -->
            <?php if (!empty($orders)): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Recent Orders</h6>
                    <a href="myorders.php" class="btn btn-outline-primary btn-sm">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                                <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                                <td><span class="badge status-<?= $order['status'] ?> px-2 py-1"><?= ucfirst($order['status']) ?></span></td>
                                <td><a href="order_detail.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-state py-5">
                <i class="bi bi-bag-x"></i>
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here</p>
                <a href="/gaming_hub/" class="btn btn-primary mt-2">Start Shopping</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
