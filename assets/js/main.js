$(document).ready(function () {
    console.log('Document ready - main.js');

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Add to cart buttons
    $(document).on('click', '.add-to-cart', function (e) {
        e.preventDefault();

        const productId = $(this).data('id');
        const quantity = $(this).closest('.product-actions').find('.quantity').val() || 1;

        // ✅ Use absolute path to avoid relative path issues
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
                    window.location.href = '/gaming_hub/user/auth/login.php';
                    exit();
                }
            }
        });

    });

    // Update cart count on page load
    // updateCartCount();
});

function addToCart(productId, quantity = 1) {
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: {
            action: 'add',
            product_id: productId,
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
    });
}

function addSessionCart(productId, quantity = 1) {
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: {
            action: 'addSessionCart',
            product_id: productId,
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
