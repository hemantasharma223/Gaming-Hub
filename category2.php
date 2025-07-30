<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: /");
    exit();
}

$categoryId = (int) $_GET['id'];

// Get category info
$category = executeQuery("SELECT * FROM main_categories WHERE category_id = ? AND is_active = TRUE", [$categoryId])
    ->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: /");
    exit();
}

// Get subcategories for this category
$subcategories = executeQuery("SELECT * FROM subcategories WHERE category_id = ? AND is_active = TRUE ORDER BY name", [$categoryId])
    ->fetchAll(PDO::FETCH_ASSOC);

// Get products in this category
$products = executeQuery("SELECT p.* FROM products p
                         JOIN subcategories s ON p.subcategory_id = s.subcategory_id
                         WHERE s.category_id = ? AND p.is_active = TRUE
                         ORDER BY p.created_at DESC", [$categoryId])
    ->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/gaming_hub/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $category['name'] ?></li>
        </ol>
    </nav>

    <h1 class="mb-4"><?= $category['name'] ?></h1>

    <?php if (!empty($subcategories)): ?>
        <div class="mb-5">
            <h4 class="mb-3">Subcategories</h4>
            <div class="row">
                <?php foreach ($subcategories as $subcategory): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 category-card">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= $subcategory['name'] ?></h5>
                                <a href="subcategory.php?subcategory=<?= $subcategory['subcategory_id'] ?>&id=<?= $_GET['id'] ?>
" class="btn btn-sm btn-outline-primary">View Products</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Products</h4>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown"
                    data-bs-toggle="dropdown">
                    Sort By
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?id=<?= $categoryId ?>&sort=newest">Newest First</a></li>
                    <li><a class="dropdown-item" href="?id=<?= $categoryId ?>&sort=price_asc">Price: Low to High</a>
                    </li>
                    <li><a class="dropdown-item" href="?id=<?= $categoryId ?>&sort=price_desc">Price: High to Low</a>
                    </li>
                </ul>
            </div>
        </div>

        <?php if (!empty($products)): ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 product-card">
                            <?php if ($product['discount_price']): ?>
                                <span class="badge bg-danger position-absolute" style="top: 10px; right: 10px;">
                                    Save
                                    <?= number_format(($product['price'] - $product['discount_price']) / $product['price'] * 100, 0) ?>%
                                </span>
                            <?php endif; ?>
                            <img src="assets/uploads/products/<?= $product['image'] ?>" class="card-img-top"
                                alt="<?= $product['name'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $product['name'] ?></h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($product['discount_price']): ?>
                                            <span class="text-danger fw-bold">Rs.
                                                <?= number_format($product['discount_price'], 2) ?></span>
                                            <span class="text-decoration-line-through text-muted small">Rs.
                                                <?= number_format($product['price'], 2) ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold">Rs. <?= number_format($product['price'], 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary add-to-cart"
                                        data-id="<?= $product['product_id'] ?>">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-primary w-100">View
                                    Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No products found in this category.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>