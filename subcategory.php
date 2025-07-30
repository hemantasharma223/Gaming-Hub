<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: /gamming_hub/");
    exit();
}

$subcategoryId = (int) $_GET['id'];

// Get subcategory info
$subcategory = executeQuery("SELECT sc.*, mc.name AS category_name 
                            FROM subcategories sc
                            JOIN main_categories mc ON sc.category_id = mc.category_id
                            WHERE sc.subcategory_id = ? AND sc.is_active = TRUE", [$subcategoryId])
    ->fetch(PDO::FETCH_ASSOC);

if (!$subcategory) {
    header("Location: /");
    exit();
}

// Handle sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$orderBy = 'p.created_at DESC';
switch ($sort) {
    case 'price_asc':
        $orderBy = 'COALESCE(p.discount_price, p.price) ASC';
        break;
    case 'price_desc':
        $orderBy = 'COALESCE(p.discount_price, p.price) DESC';
        break;
    case 'newest':
    default:
        $orderBy = 'p.created_at DESC';
        break;
}

// Get products in this subcategory
$products = executeQuery("SELECT p.* FROM products p
                        WHERE p.subcategory_id = ? AND p.is_active = TRUE
                        ORDER BY $orderBy", [$subcategoryId])
    ->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/gaming_hub/">Home</a></li>
            <li class="breadcrumb-item"><a
                    href="category.php?id=<?= $subcategory['category_id'] ?>"><?= $subcategory['category_name'] ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page"><?= $subcategory['name'] ?></li>
        </ol>
    </nav>

    <h1 class="mb-4"><?= $subcategory['name'] ?></h1>

    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Products</h4>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown"
                    data-bs-toggle="dropdown">
                    Sort By
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?id=<?= $subcategoryId ?>&sort=newest">Newest First</a></li>
                    <li><a class="dropdown-item" href="?id=<?= $subcategoryId ?>&sort=price_asc">Price: Low to High</a>
                    </li>
                    <li><a class="dropdown-item" href="?id=<?= $subcategoryId ?>&sort=price_desc">Price: High to Low</a>
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
                No products found in this subcategory.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>