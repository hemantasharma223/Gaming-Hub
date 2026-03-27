$(document).ready(function () {
    console.log('Document ready - main.js');

    // ── Theme Toggle ──────────────────────────────────────
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('gh_theme', theme);
        if (theme === 'light') {
            $('#theme-icon').removeClass('bi-moon-stars-fill').addClass('bi-sun-fill');
            $('#theme-toggle').attr('title', 'Switch to Dark Mode');
        } else {
            $('#theme-icon').removeClass('bi-sun-fill').addClass('bi-moon-stars-fill');
            $('#theme-toggle').attr('title', 'Switch to Light Mode');
        }
    }

    // Init icon from current theme
    const savedTheme = localStorage.getItem('gh_theme') || 'dark';
    applyTheme(savedTheme);

    $('#theme-toggle').on('click', function () {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });
    // ─────────────────────────────────────────────────────

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Add to cart buttons
    $(document).on('click', '.add-to-cart', function (e) {
        e.preventDefault();

        const productId = $(this).data('id');
        const quantity = $(this).closest('.product-actions').find('.quantity').val() || 1;

        $.ajax({
            url: '/gaming_hub/api/cart.php',
            method: 'POST',
            data: { action: 'checklogin', },
            dataType: 'json',
            success: function (response) {
                if (response.logged_in) {
                    console.log('User is logged in, adding to cart:', productId, 'Quantity:', quantity);
                    addToCart(productId, quantity);
                } else {
                    console.log('Guest user, adding to session cart:', productId, 'Quantity:', quantity);
                    addSessionCart(productId, quantity);
                }
            }
        });

    });

    // Quantity Increment/Decrement
    $(document).on('click', '.increment', function (e) {
        e.preventDefault();
        const input = $(this).siblings('.quantity');
        let currentVal = parseInt(input.val()) || 1;
        let maxVal = parseInt(input.attr('max')) || 999;
        if (currentVal < maxVal) {
            input.val(currentVal + 1);
        } else {
            showAlert('Cannot exceed available stock', 'warning');
        }
    });

    $(document).on('click', '.decrement', function (e) {
        e.preventDefault();
        const input = $(this).siblings('.quantity');
        let currentVal = parseInt(input.val()) || 1;
        let minVal = parseInt(input.attr('min')) || 1;
        if (currentVal > minVal) {
            input.val(currentVal - 1);
        }
    });

    // Update cart count on page load
    updateCartCount();
});

function addToCart(productId, quantity = 1) {
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: { action: 'add', product_id: productId, quantity: quantity },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('.cart-count').text(response.cart_count);
                showAlert('Product added to cart!', 'success');
                trackActivity(productId, 'cart');
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function () {
            showAlert('Failed to add product to cart. Please try again.', 'danger');
        }
    });
}

function addSessionCart(productId, quantity = 1) {
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: { action: 'addSessionCart', product_id: productId, quantity: quantity },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('.cart-count').text(response.cart_count);
                showAlert('Product added to cart!', 'success');
                trackActivity(productId, 'cart');
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function () {
            showAlert('Failed to add product to cart. Please try again.', 'danger');
        }
    });
}

/**
 * Fire-and-forget activity tracker — logs to user_activity via API.
 * Works for both guests (user_id = null stored) and logged-in users.
 */
function trackActivity(productId, actionType) {
    $.ajax({
        url: '/gaming_hub/api/track_activity.php',
        method: 'POST',
        data: { product_id: productId, action_type: actionType },
        dataType: 'json'
        // no success/error handlers needed — fire and forget
    });
}

function showAlert(message, type) {
    const alert = $(`
        <div class="alert alert-${type} alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    $('body').append(alert);
    setTimeout(() => alert.alert('close'), 3000);
}

function updateCartCount() {
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: { action: 'get_count' },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                const count = response.count || 0;
                $('.cart-count').text(count > 0 ? count : '');
            }
        }
    });
}
