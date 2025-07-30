<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: /admin/index.php");
    exit();
}

// Get pending orders
$pendingOrders = executeQuery("SELECT o.*, u.email, u.full_name 
                              FROM orders o
                              JOIN users u ON o.user_id = u.user_id
                              WHERE o.status = 'pending'
                              ORDER BY o.order_date DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Pending Orders</h6>
                    <div>
                        <a href="all.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
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
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingOrders as $order): ?>
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
                                        <td class="align-middle text-center">
                                            <a href="view.php?id=<?= $order['order_id'] ?>"
                                                class="btn btn-sm btn-outline-info mb-0">
                                                <i class="bi bi-eye"></i> View
                                            </a>
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
            columnDefs: [
                { orderable: false, targets: [4] }
            ]
        });

        // Process order
        $('.process-order').click(function () {
            const orderId = $(this).data('id');
            if (confirm('Are you sure you want to mark this order as processing?')) {
                $.ajax({
                    url: '/api/admin.php',
                    method: 'POST',
                    data: { action: 'process_order', order_id: orderId },
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