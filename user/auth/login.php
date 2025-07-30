<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (isLoggedIn()) {
    header("Location: /gaming_hub/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);

    $sql = "SELECT user_id, email, password,is_active FROM users WHERE email = ? ";
    $stmt = executeQuery($sql, [$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user['is_active']) {
        $error = "This account is deactive please contact admin .";
    } elseif ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];

        // ✅ Insert/update sessioncart into cart table
        if (!empty($_SESSION['sessioncart'])) {
            echo ` <script>$.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: {
            action: 'add',
            product_id: {$_SESSION['sessioncart']['']},
            quantity: quantity
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('.cart-count').text(response.cart_count);
                showAlert('Product added to cart!', 'success');
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function () {
            showAlert('Failed to add product to cart. Please try again.', 'danger');
        }
    });</script>`;

            // Optional: Clear session cart after syncing
            unset($_SESSION['sessioncart']);
        }

        // Redirect
        $redirect = $_SESSION['redirect_url'] ?? '/gaming_hub/';
        unset($_SESSION['redirect_url']);
        header("Location: $redirect");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- === Login Form UI === -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <p class="mb-0"><?= $_SESSION['success_message'] ?></p>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <p class="mb-0"><?= $error ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>