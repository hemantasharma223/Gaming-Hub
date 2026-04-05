<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$productId = (int)$_GET['id'];

$sql = "SELECT p.*, s.name as subcategory_name, m.name as category_name, m.category_id as category_id,
               s.subcategory_id as subcategory_id
        FROM products p
        JOIN subcategories s ON p.subcategory_id = s.subcategory_id
        JOIN main_categories m ON s.category_id = m.category_id
        WHERE p.product_id = ? AND p.is_active = TRUE";
$stmt = executeQuery($sql, [$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php?error=Product not found");
    exit();
}

$relatedProducts    = getSimilarProducts($productId, 4);
$customersAlsoBought = getCustomersAlsoBought($productId, 4);

$pageTitle = htmlspecialchars($product['name']);
require_once 'includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="category.php?id=<?= $product['category_id'] ?>"><?= $product['category_name'] ?></a></li>
            <li class="breadcrumb-item"><a href="#"><?= $product['subcategory_name'] ?></a></li>
            <li class="breadcrumb-item active"><?= $product['name'] ?></li>
        </ol>
    </nav>

    <!-- Product Main -->
    <div class="row g-5 mb-5">
        <!-- Image -->
        <div class="col-md-5">
            <div class="product-image-container">
                <img src="assets/uploads/products/<?= $product['image'] ?>"
                     alt="<?= $product['name'] ?>" class="img-fluid" style="max-height:400px;object-fit:contain;width:100%">
            </div>
        </div>

        <!-- Info -->
        <div class="col-md-7">
            <span class="tag mb-2 d-inline-block"><?= $product['category_name'] ?> / <?= $product['subcategory_name'] ?></span>
            <h1 class="product-title mb-3"><?= $product['name'] ?></h1>

            <!-- Price -->
            <div class="d-flex align-items-baseline gap-3 mb-4">
                <?php if ($product['discount_price']): ?>
                    <span class="price-main">Rs. <?= number_format($product['discount_price'], 2) ?></span>
                    <span class="price-original">Rs. <?= number_format($product['price'], 2) ?></span>
                    <span class="badge bg-danger">
                        Save <?= number_format((($product['price'] - $product['discount_price']) / $product['price'] * 100), 0) ?>%
                    </span>
                <?php else: ?>
                    <span class="price-main">Rs. <?= number_format($product['price'], 2) ?></span>
                <?php endif; ?>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge status-delivered px-3 py-2 fs-small">
                        <i class="bi bi-check-circle-fill me-1"></i> In Stock (<?= $product['stock'] ?> available)
                    </span>
                <?php else: ?>
                    <span class="badge status-cancelled px-3 py-2 fs-small">
                        <i class="bi bi-x-circle-fill me-1"></i> Out of Stock
                    </span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <?php if ($product['description']): ?>
            <div class="mb-4" style="color:var(--text-muted);font-size:0.95rem;line-height:1.7">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
            <?php endif; ?>

            <!-- Quantity -->
            <?php if ($product['stock'] > 0): ?>
            <div class="product-actions">
                <div class="mb-4">
                    <label class="form-label">Quantity</label>
                    <div class="input-group" style="max-width:160px">
                        <button class="btn btn-outline-secondary decrement" type="button">−</button>
                        <input type="number" class="form-control text-center quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button class="btn btn-outline-secondary increment" type="button">+</button>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="d-flex gap-3 flex-wrap mb-4">
                    <button class="btn btn-primary px-5 add-to-cart" data-id="<?= $product['product_id'] ?>">
                        <i class="bi bi-bag-plus me-2"></i>Add to Cart
                    </button>
                    <a href="user/cart.php" class="btn btn-outline-primary px-4">
                        <i class="bi bi-bag-heart me-2"></i>View Cart
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Meta Table -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Product Details</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table product-meta-table mb-0">
                        <tr><th>Category</th><td><?= $product['category_name'] ?></td></tr>
                        <tr><th>Subcategory</th><td><?= $product['subcategory_name'] ?></td></tr>
                        <tr><th>Stock</th><td><?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></td></tr>
                        <?php if ($product['tags']): ?>
                        <tr><th>Tags</th><td><?php foreach(explode(',', $product['tags']) as $t): ?><span class="tag me-1"><?= trim($t) ?></span><?php endforeach; ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <hr class="glow-divider">

    <!-- Customers Also Bought -->
    <?php if (!empty($customersAlsoBought)): ?>
    <div class="mb-5">
        <h3 class="section-title"><i class="bi bi-people-fill" style="color:var(--neon-cyan)"></i> Customers Also Bought</h3>
        <div class="row g-4">
            <?php foreach ($customersAlsoBought as $p): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100">
                        <img src="assets/uploads/products/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="card-body">
                            <h6 class="card-title text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                            <div class="fw-bold" style="color:var(--neon-cyan)">Rs. <?= number_format($p['discount_price'] ?: $p['price'], 2) ?></div>
                        </div>
                        <div class="card-footer py-2">
                            <a href="product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-primary w-100">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <hr class="glow-divider">
    <?php endif; ?>

    <!-- Similar Products -->
    <?php if (!empty($relatedProducts)): ?>
    <div class="mb-5">
        <h3 class="section-title"><i class="bi bi-grid-fill" style="color:var(--accent-light)"></i> Similar Products</h3>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $p): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100">
                        <img src="assets/uploads/products/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="card-body">
                            <h6 class="card-title text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                            <div class="fw-bold" style="color:var(--neon-cyan)">Rs. <?= number_format($p['discount_price'] ?: $p['price'], 2) ?></div>
                        </div>
                        <div class="card-footer py-2">
                            <a href="product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-primary w-100">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch('/gaming_hub/api/track_activity.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=<?= $productId ?>&action_type=view'
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>