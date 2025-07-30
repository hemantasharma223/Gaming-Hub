<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: admin/index.php");
    exit();
}

// Get all categories
$categories = executeQuery("SELECT * FROM main_categories ORDER BY name")
              ->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Manage Categories</h6>
                    <a href="add.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Add New Category
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="categories-table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Image</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm"><?= $category['name'] ?></h6>
                                                <p class="text-xs text-secondary mb-0"><?= $category['slug'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($category['image']): ?>
                                            <img src="../../assets/uploads/categories/<?= $category['image'] ?>" alt="<?= $category['name'] ?>" class="me-3 img-fluid" 
                                            style="max-width: 60px; height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-xs text-secondary">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm <?= $category['is_active'] ? 'bg-gradient-success' : 'bg-gradient-secondary' ?>">
                                            <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="edit.php?id=<?= $category['category_id'] ?>" class="btn btn-sm btn-outline-info mb-0">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger mb-0 delete-category" 
                                                data-id="<?= $category['category_id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <a href="subcategories.php?id=<?= $category['category_id'] ?>" class="btn btn-sm btn-outline-primary mb-0">
                                            <i class="bi bi-list-ul"></i> Subcategories
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
$(document).ready(function() {
    // Initialize DataTable
    $('#categories-table').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [3] }
        ]
    });
    
    // Delete category
    $('.delete-category').click(function() {
        const categoryId = $(this).data('id');
        if (confirm('Are you sure you want to delete this category? All subcategories and products will also be deleted.')) {
            $.ajax({
                url: '/api/admin.php',
                method: 'POST',
                data: { action: 'delete_category', category_id: categoryId },
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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>