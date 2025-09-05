    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Gaming Hub</h5>
                    <p>Your one-stop shop for all gaming needs. Consoles, games, accessories and more.</p>
                </div>
                <div class="col-md-2">
                    <h5>Shop</h5>
                    <ul class="list-unstyled">
                        <li><a href="/">Home</a></li>
                        <?php foreach ($categories as $category): ?>
                        <li><a href="category.php?id=<?= $category['category_id'] ?>"><?= $category['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Account</h5>
                    <ul class="list-unstyled">
                        <?php if (isLoggedIn()): ?>
                        <li><a href="/user/myorders.php">My Orders</a></li>
                        <li><a href="/user/auth/logout.php">Logout</a></li>
                        <?php else: ?>
                        <li><a href="/user/auth/login.php">Login</a></li>
                        <li><a href="/user/auth/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address>
                        <p><i class="bi bi-geo-alt"></i> 123 Gaming Street, Kathmandu</p>
                        <p><i class="bi bi-telephone"></i> +977 9869837027</p>
                        <p><i class="bi bi-envelope"></i> info@gaminghub.com</p>
                    </address>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> kshitishbhurtel.com.np . All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/gaming_hub/assets/js/main.js"></script>
</body>
</html>