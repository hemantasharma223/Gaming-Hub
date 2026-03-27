<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (isLoggedIn()) {
    header("Location: /gaming_hub/index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);

    $stmt = executeQuery("SELECT user_id, email, password, is_active FROM users WHERE email = ?", [$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !$user['is_active']) {
        $error = "This account has been deactivated. Please contact admin.";
    } elseif ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];

        if (!empty($_SESSION['sessioncart'])) {
            foreach ($_SESSION['sessioncart'] as $productId => $quantity) {
                $cartItem = executeQuery("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?", [$user['user_id'], $productId])->fetch();
                if ($cartItem) {
                    executeQuery("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?", [$cartItem['quantity'] + $quantity, $user['user_id'], $productId]);
                } else {
                    executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", [$user['user_id'], $productId, $quantity]);
                }
            }
            unset($_SESSION['sessioncart']);
        }

        $redirect = $_SESSION['redirect_url'] ?? '/gaming_hub/';
        unset($_SESSION['redirect_url']);
        header("Location: $redirect");
        exit();
    } else {
        $error = "Invalid email or password. Please try again.";
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center" style="min-height:80vh;padding:40px 16px">
    <div style="width:100%;max-width:460px">
        <div class="text-center mb-4">
            <i class="bi bi-controller" style="font-size:3rem;color:var(--accent-light)"></i>
            <h2 class="mt-2" style="font-family:'Rajdhani',sans-serif;font-size:2rem">Welcome Back</h2>
            <p class="text-muted">Sign in to your Gaming Hub account</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= $error ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= $_SESSION['success_message'] ?></div>
                <?php unset($_SESSION['success_message']); endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>

                <hr class="glow-divider my-4">
                <div class="text-center">
                    <p class="text-muted mb-0">Don't have an account? <a href="register.php" style="color:var(--accent-light);font-weight:600">Create one</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>