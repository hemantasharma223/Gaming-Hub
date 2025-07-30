<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Get all products with category and subcategory info
$products = executeQuery("SELECT p.*, m.name as category_name, s.name as subcategory_name 
                          FROM products p
                          JOIN subcategories s ON p.subcategory_id = s.subcategory_id
                          JOIN main_categories m ON s.category_id = m.category_id
                          ORDER BY p.created_at DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Products Management</h6>
                    <a href="add.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Add New Product
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="products-table">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Product</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Category</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Price</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Stock</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Status</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="../../assets/uploads/products/<?= $product['image'] ?>"
                                                        class="me-3 img-fluid"
                                                        style="max-width: 60px; height: 60px; object-fit: cover; alt="
                                                        <?= $product['name'] ?>">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?= $product['name'] ?></h6>
                                                    <p class="text-xs text-secondary mb-0">
                                                        <?= substr($product['description'], 0, 50) ?>...
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><?= $product['category_name'] ?></p>
                                            <p class="text-xs text-secondary mb-0"><?= $product['subcategory_name'] ?></p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <?php if ($product['discount_price']): ?>
                                                <span class="text-danger">Rs.
                                                    <?= number_format($product['discount_price'], 2) ?></span>
                                                <span class="text-decoration-line-through text-xs text-muted">Rs.
                                                    <?= number_format($product['price'], 2) ?></span>
                                            <?php else: ?>
                                                <span>Rs. <?= number_format($product['price'], 2) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span
                                                class="text-secondary text-xs font-weight-bold"><?= $product['stock'] ?></span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span
                                                class="badge badge-sm <?= $product['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                            <span
                                                class="badge badge-sm <?= $product['is_featured'] ? 'bg-info' : 'bg-warning' ?>">
                                                <?= $product['is_featured'] ? 'Featured' : 'Regular' ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="edit.php?id=<?= $product['product_id'] ?>"
                                                class="btn btn-sm btn-outline-info mb-0">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $product['product_id'] ?>"
                                                onclick="return confirm('Are you sure you want to delete this product?')"
                                                class="btn btn-sm btn-outline-danger mb-0">
                                                <i class="bi bi-trash"></i>
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
        $('#products-table').DataTable({
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [5] }
            ]
        });

        // Delete product
        $('.delete-product').click(function () {
            const productId = $(this).data('id');
            if (confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    url: '/api/admin.php',
                    method: 'POST',
                    data: { action: 'delete_product', product_id: productId },
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