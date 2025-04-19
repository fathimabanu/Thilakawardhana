<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - ShopEasy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <section class="cart-header">
            <div class="container">
                <h1>Shopping Cart</h1>
                <div class="breadcrumb">
                    <a href="index.php">Home</a> / <span>Cart</span>
                </div>
            </div>
        </section>

        <section class="cart-content">
            <div class="container">
                <?php
                    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                ?>
                <div class="cart-grid">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th class="product-col">Product</th>
                                    <th class="price-col">Price</th>
                                    <th class="quantity-col">Quantity</th>
                                    <th class="subtotal-col">Subtotal</th>
                                    <th class="remove-col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $total = 0;
                                    foreach ($_SESSION['cart'] as $id => $item) {
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $total += $subtotal;
                                ?>
                                <tr>
                                    <td class="product-col">
                                        <div class="product-info">
                                            <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                            <div>
                                                <h3><?php echo $item['name']; ?></h3>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price-col">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="quantity-col">
                                        <div class="quantity-selector">
                                            <button class="quantity-btn minus"><i class="fas fa-minus"></i></button>
                                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-id="<?php echo $id; ?>">
                                            <button class="quantity-btn plus"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </td>
                                    <td class="subtotal-col">$<?php echo number_format($subtotal, 2); ?></td>
                                    <td class="remove-col">
                                        <button class="remove-item" data-id="<?php echo $id; ?>"><i class="fas fa-trash-alt"></i></button>
                                    </td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                        
                        <div class="cart-actions">
                            <a href="shop.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                            <button id="clear-cart" class="btn btn-danger">Clear Cart</button>
                            <button id="update-cart" class="btn">Update Cart</button>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <h2>Cart Summary</h2>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="cart-total">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <div class="coupon">
                            <h3>Apply Coupon</h3>
                            <div class="coupon-form">
                                <input type="text" placeholder="Enter coupon code">
                                <button class="btn">Apply</button>
                            </div>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                    </div>
                </div>
                <?php
                    } else {
                ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any products to your cart yet.</p>
                    <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                </div>
                <?php
                    }
                ?>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Remove item from cart
            const removeButtons = document.querySelectorAll('.remove-item');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-id');
                    
                    fetch('includes/cart_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=remove&product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to update the cart
                            window.location.reload();
                        } else {
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error removing item from cart', 'error');
                    });
                });
            });
            
            // Clear cart
            const clearCartButton = document.getElementById('clear-cart');
            if (clearCartButton) {
                clearCartButton.addEventListener('click', function() {
                    if (confirm('Are you sure you want to clear your cart?')) {
                        fetch('includes/cart_actions.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=clear'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Error clearing cart', 'error');
                        });
                    }
                });
            }
            
            // Update cart
            const updateCartButton = document.getElementById('update-cart');
            if (updateCartButton) {
                updateCartButton.addEventListener('click', function() {
                    showNotification('Cart updated successfully', 'success');
                });
            }
        });
    </script>
</body>
</html> 