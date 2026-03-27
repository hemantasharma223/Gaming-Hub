<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email            = sanitize($_POST['email']);
    $password         = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    $full_name        = sanitize($_POST['full_name']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($password) < 6)                       $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password)              $errors[] = "Passwords do not match.";

    $stmt = executeQuery("SELECT email FROM users WHERE email = ?", [$email]);
    if ($stmt->rowCount() > 0) $errors[] = "Email already registered.";

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        executeQuery("INSERT INTO users (email, password, full_name) VALUES (?, ?, ?)", [$email, $hashed, $full_name]);
        $_SESSION['success_message'] = "Account created successfully! Please login.";
        header("Location: login.php");
        exit();
    }
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center" style="min-height:80vh;padding:40px 16px">
    <div style="width:100%;max-width:480px">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus" style="font-size:3rem;color:var(--accent-light)"></i>
            <h2 class="mt-2" style="font-family:'Rajdhani',sans-serif;font-size:2rem">Create Account</h2>
            <p class="text-muted">Join Gaming Hub and level up your experience</p>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-3">
                    <?php foreach($errors as $err): ?><p class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i><?= $err ?></p><?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Dipesh Karki" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <small class="text-muted">(min 6 chars)</small></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" minlength="6" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••" minlength="6" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </form>

                <hr class="glow-divider my-4">
                <div class="text-center">
                    <p class="text-muted mb-0">Already have an account? <a href="login.php" style="color:var(--accent-light);font-weight:600">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>