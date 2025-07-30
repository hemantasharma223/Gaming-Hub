<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Get all categories
$categories = executeQuery("SELECT * FROM main_categories WHERE is_active = TRUE ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Handle filters
$selectedCategory = $_GET['category'] ?? '';
$selectedSubcategory = $_GET['subcategory'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Sorting logic
switch ($sort) {
    case 'price_asc':
        $orderBy = 'COALESCE(p.discount_price, p.price) ASC';
        break;
    case 'price_desc':
        $orderBy = 'COALESCE(p.discount_price, p.price) DESC';
        break;
    default:
        $orderBy = 'p.created_at DESC';
        break;
}

// Base query
$sql = "SELECT p.* FROM products p
        JOIN subcategories s ON p.subcategory_id = s.subcategory_id
        JOIN main_categories c ON s.category_id = c.category_id
        WHERE p.is_active = TRUE";

// Parameters
$params = [];

// Filter: Category
if (!empty($selectedCategory)) {
    $sql .= " AND c.category_id = ?";
    $params[] = $selectedCategory;
}

// Filter: Subcategory
if (!empty($selectedSubcategory)) {
    $sql .= " AND s.subcategory_id = ?";
    $params[] = $selectedSubcategory;
}

// Filter: Search
if (!empty($search)) {
    $sql .= " AND p.name LIKE ?";
    $params[] = '%' . $search . '%';
}

$sql .= " ORDER BY $orderBy";

$products = executeQuery($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">All Products</h1>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="category" class="form-label">Category</label>
            <select id="category" name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $selectedCategory == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= $cat['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selectedCategory): ?>
            <?php
                $subcategories = executeQuery("SELECT * FROM subcategories WHERE category_id = ? AND is_active = TRUE ORDER BY name", [$selectedCategory])
                                ->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="col-md-3">
                <label for="subcategory" class="form-label">Subcategory</label>
                <select id="subcategory" name="subcategory" class="form-select" onchange="this.form.submit()">
                    <option value="">All Subcategories</option>
                    <?php foreach ($subcategories as $sub): ?>
                        <option value="<?= $sub['subcategory_id'] ?>" <?= $selectedSubcategory == $sub['subcategory_id'] ? 'selected' : '' ?>>
                            <?= $sub['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="col-md-3">
            <label for="search" class="form-label">Search</label>
            <input type="text" id="search" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Product name">
        </div>

        <div class="col-md-2">
            <label for="sort" class="form-label">Sort By</label>
            <select name="sort" id="sort" class="form-select" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Go</button>
        </div>
    </form>

    <!-- Products -->
    <?php if (!empty($products)): ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 product-card">
                        <?php if ($product['discount_price']): ?>
                            <span class="badge bg-danger position-absolute" style="top: 10px; right: 10px;">
                                Save <?= number_format(($product['price'] - $product['discount_price']) / $product['price'] * 100, 0) ?>%
                            </span>
                        <?php endif; ?>
                        <img src="assets/uploads/products/<?= $product['image'] ?>" class="card-img-top" alt="<?= $product['name'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $product['name'] ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($product['discount_price']): ?>
                                        <span class="text-danger fw-bold">Rs. <?= number_format($product['discount_price'], 2) ?></span>
                                        <span class="text-decoration-line-through text-muted small">Rs. <?= number_format($product['price'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold">Rs. <?= number_format($product['price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-sm btn-outline-primary add-to-cart" data-id="<?= $product['product_id'] ?>">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No products found.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
