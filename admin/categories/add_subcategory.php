<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: /admin/index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage.php");
    exit();
}

$categoryId = (int)$_GET['id'];

// Verify category exists
$category = executeQuery("SELECT * FROM main_categories WHERE category_id = ?", [$categoryId])
            ->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: manage.php");
    exit();
}

$errors = [];
$subcategory = [
    'name' => '',
    'slug' => '',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subcategory['name'] = sanitize($_POST['name']);
    $subcategory['slug'] = sanitize($_POST['slug']);
    $subcategory['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($subcategory['name'])) {
        $errors[] = "Subcategory name is required.";
    }
    
    if (empty($subcategory['slug'])) {
        $subcategory['slug'] = strtolower(str_replace(' ', '-', $subcategory['name']));
    } else {
        $subcategory['slug'] = strtolower(str_replace(' ', '-', $subcategory['slug']));
    }
    
    // Check if slug already exists in this category
    $slugCheck = executeQuery("SELECT subcategory_id FROM subcategories WHERE slug = ? AND category_id = ?", 
                             [$subcategory['slug'], $categoryId])->fetch();
    if ($slugCheck) {
        $errors[] = "Slug already exists in this category. Please choose a different one.";
    }
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO subcategories (category_id, name, slug, is_active) VALUES (?, ?, ?, ?)";
            executeQuery($sql, [
                $categoryId,
                $subcategory['name'],
                $subcategory['slug'],
                $subcategory['is_active']
            ]);
            
            $_SESSION['success_message'] = "Subcategory added successfully!";
            header("Location: subcategories.php?id=$categoryId");
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
                        <h6>Add Subcategory to <?= htmlspecialchars($category['name']) ?></h6>
                        <a href="subcategories.php?id=<?= $categoryId ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Subcategories
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
                    
                    <form method="POST" class="p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Subcategory Name</label>
                                    <input class="form-control" type="text" id="name" name="name" 
                                           value="<?= htmlspecialchars($subcategory['name']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug" class="form-control-label">Slug (URL-friendly)</label>
                                    <input class="form-control" type="text" id="slug" name="slug" 
                                           value="<?= htmlspecialchars($subcategory['slug']) ?>">
                                    <small class="text-muted">Leave blank to auto-generate from name</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" <?= $subcategory['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Active Subcategory</label>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Add Subcategory</button>
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

<?php require_once '../includes/admin_footer.php'; ?>