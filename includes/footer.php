    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-md-4">
                    <h5><i class="bi bi-controller me-2" style="color:var(--accent-light)"></i>Gaming Hub</h5>
                    <p style="font-size:0.9rem;line-height:1.7;">Your one-stop destination for consoles, games, and gaming accessories. Level up your gaming experience.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="fs-5"><i class="bi bi-discord"></i></a>
                        <a href="#" class="fs-5"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Shop</h5>
                    <ul class="list-unstyled">
                        <li><a href="/gaming_hub/">Home</a></li>
                        <?php foreach ($categories as $category): ?>
                        <li><a href="/gaming_hub/category.php?id=<?= $category['category_id'] ?>"><?= $category['name'] ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="/gaming_hub/all_products.php">All Products</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Account</h5>
                    <ul class="list-unstyled">
                        <?php if (isLoggedIn()): ?>
                        <li><a href="/gaming_hub/user/dashboard.php">Dashboard</a></li>
                        <li><a href="/gaming_hub/user/myorders.php">My Orders</a></li>
                        <li><a href="/gaming_hub/user/auth/logout.php">Logout</a></li>
                        <?php else: ?>
                        <li><a href="/gaming_hub/user/auth/login.php">Login</a></li>
                        <li><a href="/gaming_hub/user/auth/register.php">Register</a></li>
                        <?php endif; ?>
                        <li><a href="/gaming_hub/user/cart.php">Cart</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address style="font-style:normal;font-size:0.9rem;">
                        <p><i class="bi bi-geo-alt me-2" style="color:var(--accent-light)"></i>123 Gaming Street, Kathmandu, Nepal</p>
                        <p><i class="bi bi-telephone me-2" style="color:var(--accent-light)"></i>+977 9869837027</p>
                        <p><i class="bi bi-envelope me-2" style="color:var(--accent-light)"></i>info@gaminghub.com</p>
                    </address>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p style="font-size:0.8rem;margin:0;">&copy; <?= date('Y') ?> <a href="#" target="_blank">Gaming Hub</a>. All rights reserved. Built with <i class="bi bi-heart-fill" style="color:var(--danger)"></i></p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/gaming_hub/assets/js/main.js"></script>
</body>
</html>