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
                        <a href="manage.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Back to Categories
                        </a>
                        <a href="add_subcategory.php?id=<?= $categoryId ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg"></i> Add Subcategory
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="subcategories-table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subcategory</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Slug</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subcategories as $subcategory): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm"><?= $subcategory['name'] ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= $subcategory['slug'] ?></p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm <?= $subcategory['is_active'] ? 'bg-gradient-success' : 'bg-gradient-secondary' ?>">
                                            <?= $subcategory['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
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

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#subcategories-table').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [3] }
        ]
    });
    
    // Delete subcategory
    $('.delete-subcategory').click(function() {
        const subcategoryId = $(this).data('id');
        if (confirm('Are you sure you want to delete this subcategory? All products in this subcategory will also be deleted.')) {
            $.ajax({
                url: '/api/admin.php',
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

<?php require_once '../includes/admin_footer.php'; ?>