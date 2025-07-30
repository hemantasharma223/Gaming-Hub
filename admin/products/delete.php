<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
  header("Location: /admin/index.php");
  exit();
}

// Validate and sanitize product ID
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
  $_SESSION['error_message'] = "Invalid product ID.";
  header("Location: manage.php");
  exit();
}

// Check if product exists
$product = executeQuery("SELECT image FROM products WHERE product_id = ?", [$productId])->fetch();

if (!$product) {
  $_SESSION['error_message'] = "Product not found.";
  header("Location: manage.php");
  exit();
}

// Delete the image from disk
$imagePath = '../../assets/uploads/products/' . $product['image'];
if (!empty($product['image']) && file_exists($imagePath)) {
  unlink($imagePath);
}

// Delete the product from DB
executeQuery("DELETE FROM products WHERE product_id = ?", [$productId]);

$_SESSION['success_message'] = "Product deleted successfully.";
header("Location: manage.php");
exit();
