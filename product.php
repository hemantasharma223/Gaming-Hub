<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$productId = (int)$_GET['id'];

$sql = "SELECT p.*, s.name as subcategory_name, m.name as category_name, m.category_id as category_id
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

// Get related products
$relatedSql = "SELECT p.* FROM products p
               WHERE p.subcategory_id = ? AND p.product_id != ? AND p.is_active = TRUE
               LIMIT 4";
$relatedProducts = executeQuery($relatedSql, [$product['subcategory_id'], $productId])->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="category.php?id=<?= $product['category_id'] ?>"><?= $product['category_name'] ?></a></li>
            <li class="breadcrumb-item"><a href="#"><?= $product['subcategory_name'] ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $product['name'] ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <div class="product-image-container mb-4">
                <img src="assets/uploads/products/<?= $product['image'] ?>" 
                     alt="<?= $product['name'] ?>" class="img-fluid rounded">
            </div>
        </div>
        <div class="col-md-6">
            <h1 class="product-title mb-3"><?= $product['name'] ?></h1>
            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <?php if ($product['discount_price']): ?>
                        <span class="h3 text-danger">Rs. <?= number_format($product['discount_price'], 2) ?></span>
                        <span class="text-decoration-line-through text-muted">Rs. <?= number_format($product['price'], 2) ?></span>
                    <?php else: ?>
                        <span class="h3">Rs. <?= number_format($product['price'], 2) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($product['discount_price']): ?>
                    <span class="badge bg-danger">
                        Save <?= number_format((($product['price'] - $product['discount_price']) / $product['price'] * 100), 0) ?>%
                    </span>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <?php if ($product['stock'] > 0): ?>
                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> In Stock</span>
                <?php else: ?>
                    <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <div class="input-group" style="max-width: 150px;">
                    <button class="btn btn-outline-secondary decrement">-</button>
                    <input type="number" class="form-control text-center quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button class="btn btn-outline-secondary increment">+</button>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button class="btn btn-primary flex-grow-1 add-to-cart" data-id="<?= $product['product_id'] ?>">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                <button class="btn btn-outline-secondary">
                    <i class="bi bi-heart"></i> Wishlist
                </button>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Product Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Category</th>
                            <td><?= $product['category_name'] ?></td>
                        </tr>
                        <tr>
                            <th>Subcategory</th>
                            <td><?= $product['subcategory_name'] ?></td>
                        </tr>
                        <tr>
                            <th>Availability</th>
                            <td><?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <?php require_once 'includes/footer.php'; ?>