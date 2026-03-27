<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Default admin credentials (should be changed after first login)
$default_admin = [
    'username' => 'admin',
    'password' => password_hash('admin123', PASSWORD_DEFAULT)
];

// Check if admin table is empty and insert default admin
$stmt = executeQuery("SELECT COUNT(*) FROM admins");
if ($stmt->fetchColumn() == 0) {
    executeQuery("INSERT INTO admins (username, password) VALUES (?, ?)", 
                [$default_admin['username'], $default_admin['password']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    
    $sql = "SELECT admin_id, username, password FROM admins WHERE username = ?";
    $stmt = executeQuery($sql, [$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Hub - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts for Modern Theme -->
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Premium Design System -->
    <link rel="stylesheet" href="/gaming_hub/assets/css/style.css">
    
    <!-- Theme initialization -->
    <script>
        const savedTheme = localStorage.getItem('gh_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .login-container {
            max-width: 420px;
            margin-top: 10vh;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 login-container">
                <div class="card p-2">
                    <div class="card-header border-0 text-center bg-transparent mt-3">
                        <h2 class="section-title-center mb-0"><span><i class="bi bi-joystick"></i> Admin Panel</span></h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <p class="mb-0"><?= $error ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Enter your username" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Enter your password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-cta w-100 mt-2">LOGIN TO DASHBOARD</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>