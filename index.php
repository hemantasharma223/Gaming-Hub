<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$categories = getMainCategories();
$featuredProducts = getFeaturedProducts();
$trendingProducts = getTrendingProducts(4);

$userId = isLoggedIn() ? $_SESSION['user_id'] : null;
$recommendedProducts = [];
$recentlyViewed = [];

if ($userId) {
    $recommendedProducts = getPersonalizedRecommendations($userId, 4);
    $recentlyViewed = getRecentlyViewed($userId, 4);
}

$pageTitle = 'Your Gaming Universe';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="tag mb-3 d-inline-block"><i class="bi bi-lightning-fill me-1"></i> #1 Gaming Store in Nepal</span>
                <h1 class="mb-3">Level Up<br>Your <span style="background:linear-gradient(90deg,#9f67ff,#00d4ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Game</span></h1>
                <p class="lead mb-4">Consoles, games, and accessories — everything a gamer needs. Free delivery on orders above Rs. 5,000.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#featured-products" class="btn-cta btn">
                        <i class="bi bi-controller me-2"></i>Shop Now
                    </a>
                    <a href="all_products.php" class="btn btn-outline-secondary btn-lg">
                        Browse All
                    </a>
                </div>
            </div>
            <div class="col-md-6 text-center mt-4 mt-md-0">
                <img src="assets/images/hero-banner.png" alt="Gaming Hub" class="img-fluid" style="max-height:380px;">
            </div>
        </div>
    </div>
</section>

<!-- ① Algorithm Results -->
<div class="container my-5" id="recommendations-section">

    <!-- Recommended For You (logged in) -->
    <?php if ($userId && !empty($recommendedProducts)): ?>
        <h2 class="section-title mb-4"><i class="bi bi-stars" style="color:var(--neon-cyan)"></i> Recommended For You</h2>
        <div class="row g-4 mb-5">
            <?php foreach ($recommendedProducts as $product): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100 border-info">
                        <div class="card-header bg-info text-center fw-bold" style="font-size:0.82rem;letter-spacing:1px">
                            <i class="bi bi-star-fill me-1"></i> TOP PICK
                        </div>
                        <img src="assets/uploads/products/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name'] ?? '') ?>">
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($product['name'] ?? '') ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['category_name'] ?? '') ?></p>
                            <div class="fw-bold" style="color:var(--neon-cyan)">Rs. <?= number_format($product['discount_price'] ?: $product['price'], 2) ?></div>
                        </div>
                        <div class="card-footer py-2">
                            <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Trending Products -->
    <?php if (!empty($trendingProducts)): ?>
        <h2 class="section-title mb-4"><i class="bi bi-graph-up-arrow" style="color:var(--warning)"></i> Trending Now</h2>
        <div class="row g-4 mb-5">
            <?php foreach ($trendingProducts as $product): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100 border-warning">
                        <div class="card-header bg-warning text-center fw-bold" style="font-size:0.82rem;letter-spacing:1px">
                            <i class="bi bi-fire me-1"></i> TRENDING
                        </div>
                        <img src="assets/uploads/products/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name'] ?? '') ?>">
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($product['name'] ?? '') ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['category_name'] ?? '') ?></p>
                            <div class="fw-bold" style="color:var(--neon-cyan)">Rs. <?= number_format($product['discount_price'] ?: $product['price'], 2) ?></div>
                        </div>
                        <div class="card-footer py-2">
                            <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Recently Viewed (logged in) -->
    <?php if ($userId && !empty($recentlyViewed)): ?>
        <h2 class="section-title mb-4"><i class="bi bi-clock-history" style="color:var(--text-muted)"></i> Recently Viewed</h2>
        <div class="row g-4 mb-5">
            <?php foreach ($recentlyViewed as $product): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100">
                        <img src="assets/uploads/products/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name'] ?? '') ?>">
                        <div class="card-body">
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($product['name'] ?? '') ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['category_name'] ?? '') ?></p>
                            <div class="fw-bold" style="color:var(--neon-cyan)">Rs. <?= number_format($product['discount_price'] ?: $product['price'], 2) ?></div>
                        </div>
                        <div class="card-footer py-2">
                            <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-secondary w-100">View Again</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ② Featured Products -->
<div class="bg-section" id="featured-products">
    <div class="container">
        <h2 class="section-title"><i class="bi bi-star-fill" style="color:var(--warning)"></i> Featured Products</h2>
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100">
                        <?php if ($product['discount_price']): ?>
                            <span class="badge bg-danger position-absolute" style="top:12px;right:12px;z-index:2">
                                Save <?= number_format((($product['price'] - $product['discount_price']) / $product['price'] * 100), 0) ?>%
                            </span>
                        <?php endif; ?>
                        <img src="assets/uploads/products/<?= $product['image'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                        <div class="card-body">
                            <p class="card-text mb-1"><?= $product['category_name'] ?> &rsaquo; <?= $product['subcategory_name'] ?></p>
                            <h5 class="card-title"><?= $product['name'] ?></h5>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <?php if ($product['discount_price']): ?>
                                        <span class="fw-bold" style="color:var(--neon-cyan)">Rs. <?= number_format($product['discount_price'], 2) ?></span>
                                        <small class="text-muted text-decoration-line-through ms-1">Rs. <?= number_format($product['price'], 2) ?></small>
                                    <?php else: ?>
                                        <span class="fw-bold" style="color:var(--neon-cyan)">Rs. <?= number_format($product['price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-sm btn-outline-primary add-to-cart" data-id="<?= $product['product_id'] ?>">
                                    <i class="bi bi-bag-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-footer py-2">
                            <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-primary btn-sm w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="all_products.php" class="btn btn-outline-primary px-5">View All Products <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>

<!-- ③ Shop by Category -->
<div class="container my-5">
    <h2 class="section-title"><i class="bi bi-grid-3x3-gap-fill" style="color:var(--accent-light)"></i> Shop by Category</h2>
    <div class="row g-4">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-4 col-sm-6">
                <a href="category.php?id=<?= $category['category_id'] ?>" class="text-decoration-none">
                    <div class="card category-card">
                        <img src="assets/uploads/categories/<?= $category['image'] ?? 'default.jpg' ?>" class="card-img-top" alt="<?= $category['name'] ?>">
                        <div class="card-body text-center py-3">
                            <h5 class="card-title mb-0"><?= $category['name'] ?></h5>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Value Props -->
<div class="bg-section py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <i class="bi bi-truck fs-1 mb-3 d-block" style="color:var(--accent-light)"></i>
                <h6 class="fw-bold">Fast Delivery</h6>
                <p class="text-muted small mb-0">Free shipping on orders over Rs. 5,000</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-shield-check fs-1 mb-3 d-block" style="color:var(--neon-green)"></i>
                <h6 class="fw-bold">100% Genuine</h6>
                <p class="text-muted small mb-0">Authentic products with warranty</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-arrow-counterclockwise fs-1 mb-3 d-block" style="color:var(--warning)"></i>
                <h6 class="fw-bold">Easy Returns</h6>
                <p class="text-muted small mb-0">7-day hassle-free return policy</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-headset fs-1 mb-3 d-block" style="color:var(--neon-cyan)"></i>
                <h6 class="fw-bold">24/7 Support</h6>
                <p class="text-muted small mb-0">Expert gaming support anytime</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>