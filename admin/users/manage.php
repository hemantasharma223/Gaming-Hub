<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: /admin/index.php");
    exit();
}

// Get all users
$users = executeQuery("SELECT * FROM users ORDER BY created_at DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center border-0 bg-transparent mt-2">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-people me-2"></i>Manage Users</h5>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="users-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Registered</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 fw-bold"><?= $user['full_name'] ?? 'No name' ?></h6>
                                                    <p class="text-muted small mb-0">
                                                        <?= $user['phone'] ?? 'No phone' ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="fw-bold mb-0 small"><?= $user['email'] ?></p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span
                                                class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="small fw-bold">
                                                <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <button
                                                class="btn btn-sm btn-outline-<?= $user['is_active'] ? 'danger' : 'success' ?> mb-0 toggle-user"
                                                data-id="<?= $user['user_id'] ?>" data-status="<?= $user['is_active'] ?>">
                                                <i
                                                    class="bi bi-<?= $user['is_active'] ? 'x-circle' : 'check-circle' ?>"></i>
                                                <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                            </button>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#users-table').DataTable({
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [4] }
            ]
        });

        // Toggle user status
        $('.toggle-user').click(function () {
            const userId = $(this).data('id');
            const currentStatus = $(this).data('status');
            const newStatus = currentStatus ? 0 : 1;

            $.ajax({
                url: '/gaming_hub/api/admin.php',
                method: 'POST',
                data: {
                    action: 'toggle_user',
                    user_id: userId,
                    status: newStatus
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
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>