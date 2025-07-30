<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: /admin/index.php");
    exit();
}

$errors = [];
$category = [
    'name' => '',
    'slug' => '',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category['name'] = sanitize($_POST['name']);
    $category['slug'] = sanitize($_POST['slug']);
    $category['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($category['name'])) {
        $errors[] = "Category name is required.";
    }
    
    if (empty($category['slug'])) {
        $category['slug'] = strtolower(str_replace(' ', '-', $category['name']));
    } else {
        $category['slug'] = strtolower(str_replace(' ', '-', $category['slug']));
    }
    
    // Check if slug already exists
    $slugCheck = executeQuery("SELECT category_id FROM main_categories WHERE slug = ?", [$category['slug']])->fetch();
    if ($slugCheck) {
        $errors[] = "Slug already exists. Please choose a different one.";
    }
    
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['image'], '../../assets/uploads/categories/');
        if ($upload['success']) {
            $image = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO main_categories (name, slug, image, is_active) VALUES (?, ?, ?, ?)";
            executeQuery($sql, [
                $category['name'],
                $category['slug'],
                $image,
                $category['is_active']
            ]);
            
            $_SESSION['success_message'] = "Category added successfully!";
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
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Add New Category</h6>
                        <a href="manage.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Categories
                        </a>
                    </div>
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
                                    <label for="name" class="form-control-label">Category Name</label>
                                    <input class="form-control" type="text" id="name" name="name" 
                                           value="<?= htmlspecialchars($category['name']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug" class="form-control-label">Slug (URL-friendly)</label>
                                    <input class="form-control" type="text" id="slug" name="slug" 
                                           value="<?= htmlspecialchars($category['slug']) ?>">
                                    <small class="text-muted">Leave blank to auto-generate from name</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="image" class="form-control-label">Category Image</label>
                            <input class="form-control" type="file" id="image" name="image" accept="image/*" required>
                            <small class="text-muted">Recommended size: 800x800px, max 2MB</small>
                        </div>
                        
                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Active Category</label>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Add Category</button>
                            <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('blur', function() {
        if ($('#slug').val() === '') {
            const name = $(this).val();
            const slug = name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
            $('#slug').val(slug);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>