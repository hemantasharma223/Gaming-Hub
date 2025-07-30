<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
  header("Location: /admin/index.php");
  exit();
}

$productId = $_GET['id'] ?? null;
if (!$productId) {
  header("Location: manage.php");
  exit();
}

$product = executeQuery("SELECT * FROM products WHERE product_id = ?", [$productId])->fetch(PDO::FETCH_ASSOC);
if (!$product) {
  $_SESSION['error_message'] = "Product not found.";
  header("Location: manage.php");
  exit();
}

$categories = executeQuery("SELECT * FROM main_categories WHERE is_active = TRUE ORDER BY name")
  ->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product['name'] = sanitize($_POST['name']);
  $product['description'] = sanitize($_POST['description']);
  $product['price'] = (float) $_POST['price'];
  $product['discount_price'] = !empty($_POST['discount_price']) ? (float) $_POST['discount_price'] : null;
  $product['stock'] = (int) $_POST['stock'];
  $product['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
  $product['is_active'] = isset($_POST['is_active']) ? 1 : 0;
  $product['subcategory_id'] = (int) $_POST['subcategory_id'];

  if (empty($product['name'])) {
    $errors[] = "Product name is required.";
  }
  if (empty($product['description'])) {
    $errors[] = "Description is required.";
  }
  if ($product['price'] <= 0) {
    $errors[] = "Price must be greater than 0.";
  }
  if ($product['discount_price'] !== null && $product['discount_price'] >= $product['price']) {
    $errors[] = "Discount price must be less than regular price.";
  }
  if ($product['stock'] < 0) {
    $errors[] = "Stock cannot be negative.";
  }
  if (empty($product['subcategory_id'])) {
    $errors[] = "Subcategory is required.";
  }

  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload = uploadFile($_FILES['image'], '../../assets/uploads/products/');
    if ($upload['success']) {
      $product['image'] = $upload['filename'];
    } else {
      $errors[] = $upload['message'];
    }
  }

  if (empty($errors)) {
    try {
      $sql = "UPDATE products SET subcategory_id = ?, name = ?, description = ?, price = ?, 
                    discount_price = ?, stock = ?, is_featured = ?, is_active = ?";

      $params = [
        $product['subcategory_id'],
        $product['name'],
        $product['description'],
        $product['price'],
        $product['discount_price'],
        $product['stock'],
        $product['is_featured'],
        $product['is_active']
      ];

      if (!empty($product['image'])) {
        $sql .= ", image = ?";
        $params[] = $product['image'];
      }

      $sql .= " WHERE product_id = ?";
      $params[] = $productId;

      executeQuery($sql, $params);

      $_SESSION['success_message'] = "Product updated successfully!";
      header("Location: manage.php");
      exit();
    } catch (PDOException $e) {
      $errors[] = "Database error: " . $e->getMessage();
    }
  }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6>Edit Product</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mx-4">
              <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?= $error ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" class="p-4">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name" class="form-control-label">Product Name</label>
                  <input class="form-control" type="text" id="name" name="name"
                    value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="subcategory_id" class="form-control-label">Subcategory</label>
                  <select class="form-control" id="subcategory_id" name="subcategory_id" required>
                    <option value="">Select Subcategory</option>
                    <?php foreach ($categories as $category): ?>
                      <?php
                      $subcategories = executeQuery("SELECT * FROM subcategories 
                                                                          WHERE category_id = ? AND is_active = TRUE 
                                                                          ORDER BY name",
                        [$category['category_id']]
                      )
                        ->fetchAll(PDO::FETCH_ASSOC);
                      ?>
                      <?php if (!empty($subcategories)): ?>
                        <optgroup label="<?= htmlspecialchars($category['name']) ?>">
                          <?php foreach ($subcategories as $subcategory): ?>
                            <option value="<?= $subcategory['subcategory_id'] ?>"
                              <?= $subcategory['subcategory_id'] == $product['subcategory_id'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($subcategory['name']) ?>
                            </option>
                          <?php endforeach; ?>
                        </optgroup>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="description" class="form-control-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3"
                required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="price" class="form-control-label">Price (Rs.)</label>
                  <input class="form-control" type="number" step="0.01" id="price" name="price"
                    value="<?= $product['price'] ?>" min="0" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="discount_price" class="form-control-label">Discount Price (Rs.)</label>
                  <input class="form-control" type="number" step="0.01" id="discount_price" name="discount_price"
                    value="<?= $product['discount_price'] ?>" min="0">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="stock" class="form-control-label">Stock Quantity</label>
                  <input class="form-control" type="number" id="stock" name="stock" value="<?= $product['stock'] ?>"
                    min="0" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label class="form-control-label">Options</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                      <?= $product['is_featured'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_featured">Featured Product</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                      <?= $product['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="image" class="form-control-label">Change Image (Optional)</label>
              <input class="form-control" type="file" id="image" name="image" accept="image/*">
              <small class="text-muted">Leave blank to keep existing image.</small>
            </div>

            <div class="form-group mt-4">
              <button type="submit" class="btn btn-primary">Update Product</button>
              <a href="manage.php" class="btn btn-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>