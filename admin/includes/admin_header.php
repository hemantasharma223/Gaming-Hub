<?php
if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Hub - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Google Fonts for Modern Theme -->
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Premium Design System -->
    <link rel="stylesheet" href="/gaming_hub/assets/css/style.css">
    
    <!-- Theme initialization script (prevents FOIT) -->
    <script>
        const savedTheme = localStorage.getItem('gh_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar px-0">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-4 min-vh-100">
                    <a href="/gaming_hub/admin/dashboard.php"
                        class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-decoration-none">
                        <span class="fs-5 d-none d-sm-inline fw-bold text-primary"><i class="bi bi-joystick"></i> Gaming Hub Admin</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100 mt-3"
                        id="menu">
                        <li class="nav-item">
                            <a href="/gaming_hub/admin/dashboard.php" class="nav-link align-middle px-0">
                                <i class="bi bi-speedometer2"></i> <span
                                    class="ms-1 d-none d-sm-inline">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="/gaming_hub/admin/categories/manage.php" class="nav-link px-0 align-middle">
                                <i class="bi bi-tags"></i> <span class="ms-1 d-none d-sm-inline">Categories</span>
                            </a>
                        </li>
                        <li>
                            <a href="/gaming_hub/admin/products/manage.php" class="nav-link px-0 align-middle">
                                <i class="bi bi-controller"></i> <span class="ms-1 d-none d-sm-inline">Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="/gaming_hub/admin/orders/pending.php" class="nav-link px-0 align-middle">
                                <i class="bi bi-cart-check"></i> <span class="ms-1 d-none d-sm-inline">Orders</span>
                            </a>
                        </li>
                        <li>
                            <a href="/gaming_hub/admin/users/manage.php" class="nav-link px-0 align-middle">
                                <i class="bi bi-people"></i> <span class="ms-1 d-none d-sm-inline">Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="/gaming_hub/admin/logout.php" class="nav-link px-0 align-middle">
                                <i class="bi bi-box-arrow-right"></i> <span
                                    class="ms-1 d-none d-sm-inline">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 ms-sm-auto px-md-4 py-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom sticky-top bg-transparent align-items-center">
                    <h1 class="h2 section-title mb-0"><?= $pageTitle ?? 'Dashboard' ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0 d-flex align-items-center">
                        <div class="btn-group me-3">
                            <button id="theme-toggle" class="btn btn-outline-secondary" title="Toggle Theme">
                                <i id="theme-icon" class="bi bi-moon-stars-fill"></i>
                            </button>
                        </div>
                        <div class="btn-group">
                            <span class="text-muted d-flex align-items-center">
                                <i class="bi bi-person-circle fs-4 me-2"></i> 
                                <span class="fw-bold"><?= $_SESSION['admin_username'] ?></span>
                            </span>
                        </div>
                    </div>
                </div>