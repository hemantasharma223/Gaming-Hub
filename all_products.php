<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$categories = executeQuery("SELECT * FROM main_categories WHERE is_active = TRUE ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$selectedCategory    = $_GET['category'] ?? '';
$selectedSubcategory = $_GET['subcategory'] ?? '';
$search              = $_GET['search'] ?? '';
$sort                = $_GET['sort'] ?? 'newest';

switch ($sort) {
    case 'price_asc':  $orderBy = 'COALESCE(p.discount_price, p.price) ASC'; break;
    case 'price_desc': $orderBy = 'COALESCE(p.discount_price, p.price) DESC'; break;
    default:           $orderBy = 'p.created_at DESC';
}

$sql    = "SELECT p.*, m.name as category_name, s.name as subcategory_name FROM products p
           JOIN subcategories s ON p.subcategory_id = s.subcategory_id
           JOIN main_categories m ON s.category_id = m.category_id
           WHERE p.is_active = TRUE";
$params = [];

if (!empty($selectedCategory))    { $sql .= " AND m.category_id = ?";    $params[] = $selectedCategory; }
if (!empty($selectedSubcategory)) { $sql .= " AND s.subcategory_id = ?"; $params[] = $selectedSubcategory; }
if (!empty($search))              { $sql .= " AND p.name LIKE ?";        $params[] = '%' . $search . '%'; }
$sql .= " ORDER BY $orderBy";

$products = executeQuery($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

$subcategories = [];
if (!empty($selectedCategory)) {
    $subcategories = executeQuery("SELECT * FROM subcategories WHERE category_id = ? AND is_active = TRUE ORDER BY name", [$selectedCategory])->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'All Products';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title"><i class="bi bi-grid-3x3-gap" style="color:var(--accent-light)"></i> All Products</h1>
        <span class="text-muted"><?= count($products) ?> products found</span>
    </div>

    <!-- Filters -->
    <div class="card mb-5">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $selectedCategory == $cat['category_id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($subcategories)): ?>
                <div class="col-md-2">
                    <label for="subcategory" class="form-label">Subcategory</label>
                    <select id="subcategory" name="subcategory" class="form-select" onchange="this.form.submit()">
                        <option value="">All</option>
                        <?php foreach ($subcategories as $sub): ?>
                            <option value="<?= $sub['subcategory_id'] ?>" <?= $selectedSubcategory == $sub['subcategory_id'] ? 'selected' : '' ?>><?= $sub['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="search" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search products…">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="sort" class="form-label">Sort By</label>
                    <select name="sort" id="sort" class="form-select" onchange="this.form.submit()">
                        <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest</option>
                        <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: Low → High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Grid -->
    <?php if (!empty($products)): ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100">
                        <?php if ($product['discount_price']): ?>
                            <span class="badge bg-danger position-absolute" style="top:12px;right:12px;z-index:2">
                                Save <?= number_format(($product['price'] - $product['discount_price']) / $product['price'] * 100, 0) ?>%
                            </span>
                        <?php endif; ?>
                        <img src="assets/uploads/products/<?= $product['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <p class="card-text mb-1"><?= $product['category_name'] ?> &rsaquo; <?= $product['subcategory_name'] ?></p>
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($product['name']) ?></h5>
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
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-search"></i>
            <h3>No products found</h3>
            <p>Try adjusting your filters or search query</p>
            <a href="all_products.php" class="btn btn-outline-primary mt-2">Clear Filters</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
