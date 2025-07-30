<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// if (!isLoggedIn()) {
//     $_SESSION['redirect_url'] = 'cart.php';
//     header("Location: auth/login.php");
//     exit();
// }

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Your Shopping Cart</h1>
    
    <div id="cart-container">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading your cart...</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <a href="/gaming_hub/" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Continue Shopping
            </a>
        </div>
        <div class="col-md-6 text-end">
            <button id="checkout-btn" class="btn btn-primary" disabled>
                Proceed to Checkout <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Load cart items
    $.ajax({
            url: '/gaming_hub/api/cart.php',
            method: 'POST',
            data: { action: 'checklogin', },
            dataType: 'json',
            success: function (response) {
                if (response.logged_in) {
                    loadCart();
                } else {
                    console.log('User not logged in, loading session cart');
                    loadSessionCart();
                }
            }
        });
    
    // Checkout button
    $('#checkout-btn').click(function() {
        window.location.href = 'checkout.php';
    });
});

function loadCart() {
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: { action: 'get' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderCart(response.items, response.total);
            } else {
                $('#cart-container').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#cart-container').html('<div class="alert alert-danger">Failed to load cart. Please try again.</div>');
        }
    });
}

function loadSessionCart() {
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: { action: 'getSessionCart' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderCart(response.items, response.total);
            } else {
                $('#cart-container').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#cart-container').html('<div class="alert alert-danger">Failed to load cart. Please try again.</div>');
        }
    });
}

function renderCart(items, total) {
    if (items.length === 0) {
        $('#cart-container').html(`
            <div class="alert alert-info">
                Your cart is empty. <a href="/gaming_hub/" class="alert-link">Start shopping</a>.
            </div>
        `);
        $('#checkout-btn').prop('disabled', true);
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>`;
    
    items.forEach(item => {
        const price = item.discount_price ? parseFloat(item.discount_price) : parseFloat(item.price);
        const subtotal = price * item.quantity;
        console.log(price);
        
        html += `
            <tr id="cart-item-${item.product_id}">
                <td>
                    <div class="d-flex align-items-center">
                        <img src="/gaming_hub/assets/uploads/products/${item.image}" 
                             alt="${item.name}" class="img-thumbnail me-3" style="width: 80px;">
                        <div>
                            <h6 class="mb-0">${item.name}</h6>
                        </div>
                    </div>
                </td>
                <td>
                    ${item.discount_price ? `
                        <span class="text-danger">Rs. ${price.toFixed(2)}</span>
                        <small class="text-decoration-line-through text-muted d-block">Rs. ${item.price.toFixed(2)}</small>
                    ` : `Rs. ${price.toFixed(2)}`}
                </td>
                <td>
                    <div class="input-group" style="max-width: 120px;">
                        <button class="btn btn-outline-secondary decrement" data-id="${item.product_id}">-</button>
                        <input type="number" class="form-control text-center quantity" 
                               value="${item.quantity}" min="1" max="${item.stock || 10}">
                        <button class="btn btn-outline-secondary increment" data-id="${item.product_id}">+</button>
                    </div>
                </td>
                <td>Rs. ${subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger remove-item" data-id="${item.product_id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong>Rs. ${total.toFixed(2)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>`;
    
    $('#cart-container').html(html);
    $('#checkout-btn').prop('disabled', false);
    
    // Bind event handlers
    $('.increment').click(function() {
        const productId = $(this).data('id');
        const input = $(this).siblings('.quantity');
        const max = parseInt(input.attr('max'));
        const value = parseInt(input.val());
        
        if (value < max) {
            input.val(value + 1);
            updateCartItem(productId, value + 1);
        }
    });
    
    $('.decrement').click(function() {
        const productId = $(this).data('id');
        const input = $(this).siblings('.quantity');
        const value = parseInt(input.val());
        
        if (value > 1) {
            input.val(value - 1);
            updateCartItem(productId, value - 1);
        }
    });
    
    $('.quantity').change(function() {
        const productId = $(this).closest('tr').find('.increment').data('id');
        const value = parseInt($(this).val());
        const max = parseInt($(this).attr('max'));
        
        if (value >= 1 && value <= max) {
            updateCartItem(productId, value);
        } else {
            $(this).val(1);
            updateCartItem(productId, 1);
        }
    });
    
    $('.remove-item').click(function() {
        const productId = $(this).data('id');
         $.ajax({
            url: '/gaming_hub/api/cart.php',
            method: 'POST',
            data: { action: 'checklogin', },
            dataType: 'json',
            success: function (response) {
                if (response.logged_in) {
                    let action = 'remove';
                    removeCartItem(productId,action);
                } else {
                    let action = 'removeSessionCart';
                    console.log('User not logged in, removing from session cart');
                    removeCartItem(productId,action);
                }
            }
        });

        
    });
}

function updateCartItem(productId, quantity) {
    console.log(`Updating cart item ${productId} to quantity ${quantity}`);
        $.ajax({
            url: '/gaming_hub/api/cart.php',
            method: 'POST',
            data: { action: 'checklogin', },
            dataType: 'json',
            success: function (response) {
                console.log('Check login response:', response);
                    
                   let action = response.logged_in ? 'update' : 'updateSessionCart';

                   $.ajax({
                    url: '/gaming_hub/api/cart.php',
                    method: 'POST',
                    data: { action: action, product_id: productId, quantity: quantity },
                    dataType: 'json',
                    success: function(response) {
                        if (response.action === 'get') {
                            var action = 'get';
                            loadCart(); // Refresh the cart
                        }else if(response.action === 'getSessionCart') {
                            var action = 'getSessionCart';
                            loadSessionCart(); // Refresh the session cart
                        } else {
                            showAlert(response.message, 'danger');
                            loadCart(); // Refresh to get correct values
                        }
                    },
                    error: function(response) {
                        console.error('Error updating cart:');
                        showAlert('Failed to update cart. Please try again.', 'danger');
                      }
                  });
                              
                          }
                      });
}

function removeCartItem(productId,action) {
    console.log(`Removing cart item ${productId} with action ${action}`);
    $.ajax({
        url: '/gaming_hub/api/cart.php',
        method: 'POST',
        data: { action: action, product_id: productId },
        dataType: 'json',
        success: function(response) {
            if (response.action === 'removeSuccess') {
                $(`#cart-item-${productId}`).remove();
                updateCartCount(response.cart_count);
                loadCart(); // Refresh to check if cart is empty
                showAlert('Item removed from cart.', 'success');
            } else {
                 $(`#cart-item-${productId}`).remove();
                updateCartCount(response.cart_count);
                loadSessionCart();
                showAlert('Item removed from cart.', 'success');
            }
        },
        error: function() {
            showAlert('Failed to remove item. Please try again.', 'danger');
        }
    });
}

function updateCartCount(count) {
    $('.cart-count').text(count);
}

function showAlert(message, type) {
    const alert = $(`<div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`);
    
    $('body').append(alert);
    setTimeout(() => alert.alert('close'), 3000);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>