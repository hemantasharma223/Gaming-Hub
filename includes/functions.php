<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Upload file with validation
function uploadFile($file, $targetDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type.'];
    }
    
    if ($file['size'] > 5000000) { // 5MB max
        return ['success' => false, 'message' => 'File is too large.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file.'];
    }
}

// Get categories for navigation
function getMainCategories() {
    $sql = "SELECT * FROM main_categories WHERE is_active = TRUE ORDER BY name";
    $stmt = executeQuery($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get featured products
function getFeaturedProducts($limit = 8) {
    global $pdo;
    $sql = "SELECT p.*, m.name as category_name, s.name as subcategory_name 
            FROM products p
            JOIN subcategories s ON p.subcategory_id = s.subcategory_id
            JOIN main_categories m ON s.category_id = m.category_id
            WHERE p.is_active = TRUE AND p.is_featured = TRUE
            ORDER BY p.created_at DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get cart count for a user
function getCartCount($userId) {
    $sql = "SELECT COUNT(*) FROM cart WHERE user_id = ?";
    $stmt = executeQuery($sql, [$userId]);
    return $stmt->fetchColumn();
}

// Get product by ID
function getProductById($productId) {
    $sql = "SELECT p.*, s.name as subcategory_name, m.name as category_name 
            FROM products p
            JOIN subcategories s ON p.subcategory_id = s.subcategory_id
            JOIN main_categories m ON s.category_id = m.category_id
            WHERE p.product_id = ? AND p.is_active = TRUE";
    $stmt = executeQuery($sql, [$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get products by subcategory
function getProductsBySubcategory($subcategoryId, $limit = null) {
    $sql = "SELECT * FROM products 
            WHERE subcategory_id = ? AND is_active = TRUE 
            ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $stmt = executeQuery($sql, [$subcategoryId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>