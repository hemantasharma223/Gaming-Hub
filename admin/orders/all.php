<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: /gaming_hub/admin/index.php");
    exit();
}

// Get all orders
$orders = executeQuery("SELECT o.*, u.email, u.full_name 
                       FROM orders o
                       JOIN users u ON o.user_id = u.user_id
                       ORDER BY o.order_date DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>All Orders</h6>
                    <div>
                        <a href="pending.php" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bi bi-hourglass-split"></i> View Pending Orders
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="orders-table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Order</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Customer</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Date</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Amount</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Status</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">Order #<?= $order['order_id'] ?></h6>
                                                    <p class="text-xs text-secondary mb-0">
                                                        <?php
                                                        $itemCount = executeQuery(
                                                            "SELECT COUNT(*) FROM order_items WHERE order_id = ?",
                                                            [$order['order_id']]
                                                        )->fetchColumn();
                                                        echo $itemCount . ' item(s)';
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><?= $order['full_name'] ?></p>
                                            <p class="text-xs text-secondary mb-0"><?= $order['email'] ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">
                                                <?= date('M d, Y', strtotime($order['order_date'])) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">
                                                Rs. <?= number_format($order['total_amount'], 2) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge 
                                            <?= $order['status'] == 'pending' ? 'bg-warning' :
                                                ($order['status'] == 'processing' ? 'bg-info' :
                                                    ($order['status'] == 'shipped' ? 'bg-primary' :
                                                        ($order['status'] == 'delivered' ? 'bg-success' : 'bg-danger'))) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                            <?php if ($order['status'] == 'delivered'): ?>
                                                <p class="text-xs text-muted mb-0">
                                                    <?= date('M d, Y', strtotime($order['response_date'])) ?>
                                                </p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="view.php?id=<?= $order['order_id'] ?>"
                                                class="btn btn-sm btn-outline-info mb-0">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <?php if ($order['status'] != 'delivered' && $order['status'] != 'cancelled'): ?>

                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#orders-table').DataTable({
            responsive: true,
            order: [[2, 'desc']], // Default sort by date descending
            columnDefs: [
                { orderable: false, targets: [5] }
            ]
        });

        // Update order status
        $('.update-status').click(function () {
            const orderId = $(this).data('id');
            const action = $(this).data('status');

            if (confirm('Are you sure you want to update this order status?')) {
                $.ajax({
                    url: '/gaming_hub/api/admin.php',
                    method: 'POST',
                    data: {
                        action: 'update_order_status',
                        order_id: orderId,
                        status_action: action
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    }
                });
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>