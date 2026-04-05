<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: /admin/index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage.php");
    exit();
}

$categoryId = (int)$_GET['id'];

// Get category info
$category = executeQuery("SELECT * FROM main_categories WHERE category_id = ?", [$categoryId])
            ->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: manage.php");
    exit();
}

// Get subcategories for this category
$subcategories = executeQuery("SELECT * FROM subcategories WHERE category_id = ? ORDER BY name", [$categoryId])
                 ->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Subcategories for <?= $category['name'] ?></h6>
                    <div>
                        <a href="manage.php" class="btn btn-sm btn-outline-info me-2">
                            <i class="bi bi-arrow-left"></i> Back to Categories
                        </a>
                        <a href="add_subcategory.php?id=<?= $categoryId ?>" class="btn btn-cta btn-sm">
                            <i class="bi bi-plus-lg"></i> Add Subcategory
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="subcategories-table">
                            <thead>
                                <tr>
                                    <th>Subcategory</th>
                                    <th>Slug</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subcategories as $subcategory): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 fw-bold"><?= $subcategory['name'] ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <p class="text-muted small mb-0"><?= $subcategory['slug'] ?></p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                            <input class="form-check-input toggle-subcategory-status" type="checkbox" role="switch" 
                                                   data-id="<?= $subcategory['subcategory_id'] ?>" 
                                                   <?= $subcategory['is_active'] ? 'checked' : '' ?>
                                                   title="Toggle active status">
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="edit_subcategory.php?id=<?= $subcategory['subcategory_id'] ?>" class="btn btn-sm btn-outline-info mb-0">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger mb-0 delete-subcategory" 
                                                data-id="<?= $subcategory['subcategory_id'] ?>">
                                            <i class="bi bi-trash"></i>
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

<style>
/* Fix DataTables styling for dark mode */
[data-theme="dark"] table.dataTable tbody tr {
    background-color: transparent !important;
}
[data-theme="dark"] table.dataTable tbody tr:nth-of-type(odd) {
    background-color: rgba(255, 255, 255, 0.02) !important;
}
[data-theme="dark"] table.dataTable tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.05) !important;
}
[data-theme="dark"] .dataTables_wrapper .dataTables_filter input,
[data-theme="dark"] .dataTables_wrapper .dataTables_length select {
    background-color: var(--dark-card2);
    border: 1px solid var(--dark-border);
    color: var(--text-light);
}
</style>

<?php require_once '../includes/admin_footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#subcategories-table').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [2, 3] }
        ]
    });
    
    // Toggle subcategory status
    $(document).on('change', '.toggle-subcategory-status', function() {
        const subcategoryId = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const toggleCheckbox = $(this);
        
        $.ajax({
            url: '../../api/admin.php',
            method: 'POST',
            data: { 
                action: 'toggle_subcategory_status', 
                subcategory_id: subcategoryId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    alert(response.message || 'Failed to update subcategory status.');
                    toggleCheckbox.prop('checked', !status);
                }
            },
            error: function() {
                alert('Network error. Failed to update subcategory status.');
                toggleCheckbox.prop('checked', !status);
            }
        });
    });
    
    // Delete subcategory
    $(document).on('click', '.delete-subcategory', function() {
        const subcategoryId = $(this).data('id');
        if (confirm('Are you sure you want to delete this subcategory? All products in this subcategory will also be deleted.')) {
            $.ajax({
                url: '../../api/admin.php',
                method: 'POST',
                data: { action: 'delete_subcategory', subcategory_id: subcategoryId },
                dataType: 'json',
                success: function(response) {
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