<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$categories = getMainCategories();
$featuredProducts = getFeaturedProducts();

require_once __DIR__ . '/includes/header.php';
?>

<div class="hero-section bg-dark text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Welcome to Gaming Hub</h1>
                <p class="lead">Your one-stop shop for all gaming needs</p>
                <a href="#featured-products" class="btn btn-primary btn-lg mt-3">Shop Now</a>
            </div>
            <div class="col-md-6">
                <img src="assets/images/hero-banner.png" alt="Gaming Hub" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <h2 class="text-center mb-4">Shop by Category</h2>
    <div class="row">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 category-card">
                    <img src="assets/uploads/categories/<?= $category['image'] ?? 'default.jpg' ?>" class="card-img-top"
                        alt="<?= $category['name'] ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= $category['name'] ?></h5>
                        <a href="category.php?id=<?= $category['category_id'] ?>"
                            class="btn btn-sm btn-outline-primary">View Products</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-light py-5" id="featured-products">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 product-card">
                        <?php if ($product['discount_price']): ?>
                            <span class="badge bg-danger position-absolute" style="top: 10px; right: 10px;">
                                Save
                                <?= number_format((($product['price'] - $product['discount_price']) / $product['price'] * 100), 0) ?>%
                            </span>
                        <?php endif; ?>
                        <img src="assets/uploads/products/<?= $product['image'] ?>" class="card-img-top"
                            alt="<?= $product['name'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['name'] ?></h5>
                            <p class="card-text text-muted"><?= $product['category_name'] ?> &raquo;
                                <?= $product['subcategory_name'] ?></p>
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
        <div class="text-center mt-4">
            <a href="all_products.php" class="btn btn-outline-primary">View All Products</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>