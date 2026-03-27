<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$categories = getMainCategories();
$cartCount = getCartCount(isLoggedIn() ? $_SESSION['user_id'] : null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Hub — <?= $pageTitle ?? 'Your Gaming Store' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/gaming_hub/assets/css/style.css">
    <!-- Theme: apply before render to prevent flash -->
    <script>
        (function(){
            const t = localStorage.getItem('gh_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/gaming_hub/">
                <i class="bi bi-controller"></i> Gaming Hub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/gaming_hub/index.php">
                            <i class="bi bi-house-door"></i> Home
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/gaming_hub/category.php?id=<?= $category['category_id'] ?>">
                            <?= $category['name'] ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/gaming_hub/all_products.php">All Products</a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center gap-1">
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> My Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/gaming_hub/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="/gaming_hub/user/myorders.php"><i class="bi bi-box-seam me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/gaming_hub/user/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/gaming_hub/user/auth/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3" href="/gaming_hub/user/auth/register.php">Register</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item ms-1">
                        <a class="nav-link position-relative" href="/gaming_hub/user/cart.php">
                            <i class="bi bi-bag-heart fs-5"></i>
                            <span class="cart-count"><?= $cartCount > 0 ? $cartCount : '' ?></span>
                        </a>
                    </li>
                    <!-- Theme Toggle -->
                    <li class="nav-item ms-1">
                        <button id="theme-toggle" class="btn btn-sm d-flex align-items-center justify-content-center"
                                style="width:36px;height:36px;border-radius:50%;background:var(--dark-card2);border:1px solid var(--dark-border);color:var(--text-muted);transition:var(--transition)"
                                title="Toggle theme" aria-label="Toggle light/dark theme">
                            <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>