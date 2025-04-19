<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - ShopEasy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <?php
        require_once 'includes/db.php';
        
        // Get product ID from URL
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($product_id <= 0) {
            header('Location: shop.php');
            exit;
        }
        
        // Get product details
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            header('Location: shop.php');
            exit;
        }
        
        $product = $result->fetch_assoc();
        
        // Get product images
        $stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $images_result = $stmt->get_result();
        
        $images = [];
        while ($image = $images_result->fetch_assoc()) {
            $images[] = $image;
        }
        
        // If no additional images, use the main product image
        if (count($images) === 0) {
            $images[] = ['image' => $product['image']];
        }
        
        // Get related products
        $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
        $stmt->bind_param("ii", $product['category_id'], $product_id);
        $stmt->execute();
        $related_result = $stmt->get_result();
    ?>
    
    <main>
        <section class="product-header">
            <div class="container">
                <div class="breadcrumb">
                    <a href="index.php">Home</a> / 
                    <a href="shop.php">Shop</a> / 
                    <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a> / 
                    <span><?php echo $product['name']; ?></span>
                </div>
            </div>
        </section>

        <section class="product-details">
            <div class="container">
                <div class="product-grid">
                    <div class="product-images">
                        <div class="main-image">
                            <img src="<?php echo $images[0]['image']; ?>" alt="<?php echo $product['name']; ?>">
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                        <div class="thumbnails">
                            <?php foreach ($images as $index => $image): ?>
                            <img src="<?php echo $image['image']; ?>" alt="<?php echo $product['name']; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h1><?php echo $product['name']; ?></h1>
                        
                        <div class="product-meta">
                            <div class="product-price">
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                <span class="old-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="current-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                <?php else: ?>
                                <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-rating">
                                <?php
                                    $rating = $product['rating'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                ?>
                                <span>(<?php echo $product['reviews_count'] ?? 0; ?> reviews)</span>
                            </div>
                        </div>
                        
                        <div class="product-stock">
                            <?php if ($product['stock'] > 0): ?>
                            <span class="in-stock">In Stock (<?php echo $product['stock']; ?>)</span>
                            <?php else: ?>
                            <span class="out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-description">
                            <?php echo $product['description']; ?>
                        </div>
                        
                        <div class="product-actions">
                            <div class="quantity-selector">
                                <button class="quantity-btn minus"><i class="fas fa-minus"></i></button>
                                <input type="number" id="product-quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                <button class="quantity-btn plus"><i class="fas fa-plus"></i></button>
                            </div>
                            
                            <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                        
                        <div class="product-meta-info">
                            <div class="meta-item">
                                <span>SKU:</span>
                                <span><?php echo $product['sku'] ?? 'N/A'; ?></span>
                            </div>
                            <div class="meta-item">
                                <span>Category:</span>
                                <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a>
                            </div>
                            <div class="meta-item">
                                <span>Tags:</span>
                                <?php
                                    $tags = $product['tags'] ?? '';
                                    $tag_array = explode(',', $tags);
                                    foreach ($tag_array as $tag) {
                                        $tag = trim($tag);
                                        if (!empty($tag)) {
                                            echo '<a href="shop.php?tag=' . urlencode($tag) . '">' . $tag . '</a>';
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                        
                        <div class="social-share">
                            <span>Share:</span>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-pinterest-p"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="product-tabs">
                    <div class="tabs-header">
                        <button class="tab-btn active" data-tab="description">Description</button>
                        <button class="tab-btn" data-tab="specifications">Specifications</button>
                        <button class="tab-btn" data-tab="reviews">Reviews (<?php echo $product['reviews_count'] ?? 0; ?>)</button>
                    </div>
                    
                    <div class="tabs-content">
                        <div class="tab-panel active" id="description">
                            <h3>Product Description</h3>
                            <div class="content">
                                <?php echo $product['full_description'] ?? $product['description']; ?>
                            </div>
                        </div>
                        
                        <div class="tab-panel" id="specifications">
                            <h3>Product Specifications</h3>
                            <div class="content">
                                <table class="specs-table">
                                    <?php
                                        $specs = json_decode($product['specifications'] ?? '{}', true);
                                        if (!empty($specs) && is_array($specs)) {
                                            foreach ($specs as $key => $value) {
                                                echo '<tr><th>' . htmlspecialchars($key) . '</th><td>' . htmlspecialchars($value) . '</td></tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="2">No specifications available</td></tr>';
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="tab-panel" id="reviews">
                            <h3>Customer Reviews</h3>
                            <div class="content">
                                <?php
                                    // Get product reviews
                                    $stmt = $conn->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r 
                                                          LEFT JOIN users u ON r.user_id = u.id 
                                                          WHERE r.product_id = ? 
                                                          ORDER BY r.created_at DESC");
                                    $stmt->bind_param("i", $product_id);
                                    $stmt->execute();
                                    $reviews_result = $stmt->get_result();
                                    
                                    if ($reviews_result->num_rows > 0) {
                                        while ($review = $reviews_result->fetch_assoc()) {
                                ?>
                                <div class="review">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <span class="reviewer-name"><?php echo $review['first_name'] . ' ' . substr($review['last_name'], 0, 1) . '.'; ?></span>
                                            <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                        </div>
                                        <div class="review-rating">
                                            <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $review['rating']) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="review-content">
                                        <p><?php echo htmlspecialchars($review['review']); ?></p>
                                    </div>
                                </div>
                                <?php
                                        }
                                    } else {
                                        echo '<p>No reviews yet. Be the first to review this product!</p>';
                                    }
                                ?>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="write-review">
                                    <h4>Write a Review</h4>
                                    <form action="includes/review_handler.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        
                                        <div class="form-group">
                                            <label>Your Rating</label>
                                            <div class="rating-stars">
                                                <input type="radio" id="star5" name="rating" value="5" required>
                                                <label for="star5"><i class="far fa-star"></i></label>
                                                
                                                <input type="radio" id="star4" name="rating" value="4">
                                                <label for="star4"><i class="far fa-star"></i></label>
                                                
                                                <input type="radio" id="star3" name="rating" value="3">
                                                <label for="star3"><i class="far fa-star"></i></label>
                                                
                                                <input type="radio" id="star2" name="rating" value="2">
                                                <label for="star2"><i class="far fa-star"></i></label>
                                                
                                                <input type="radio" id="star1" name="rating" value="1">
                                                <label for="star1"><i class="far fa-star"></i></label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="review">Your Review</label>
                                            <textarea id="review" name="review" rows="5" required></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Submit Review</button>
                                    </form>
                                </div>
                                <?php else: ?>
                                <div class="login-to-review">
                                    <p>Please <a href="login.php">login</a> to write a review.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($related_result->num_rows > 0): ?>
                <div class="related-products">
                    <h2>Related Products</h2>
                    <div class="product-grid">
                        <?php while ($related = $related_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo $related['image']; ?>" alt="<?php echo $related['name']; ?>">
                            <h3><?php echo $related['name']; ?></h3>
                            <p class="price">$<?php echo number_format($related['price'], 2); ?></p>
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="btn">View Details</a>
                            <button class="btn add-to-cart" data-id="<?php echo $related['id']; ?>">Add to Cart</button>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Product quantity selector
            const quantityInput = document.getElementById('product-quantity');
            const minusBtn = document.querySelector('.quantity-btn.minus');
            const plusBtn = document.querySelector('.quantity-btn.plus');
            
            minusBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value) - 1;
                value = Math.max(1, value);
                quantityInput.value = value;
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(quantityInput.value) + 1;
                const max = parseInt(quantityInput.getAttribute('max'));
                value = Math.min(max, value);
                quantityInput.value = value;
            });
            
            // Add to cart with quantity
            const addToCartBtn = document.querySelector('.product-actions .add-to-cart');
            
            addToCartBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const quantity = parseInt(quantityInput.value);
                
                fetch('includes/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&product_id=${productId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartCount(data.cart_count);
                        showNotification('Product added to cart!', 'success');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error adding product to cart', 'error');
                });
            });
            
            // Product tabs
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanels = document.querySelectorAll('.tab-panel');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Hide all tab panels
                    tabPanels.forEach(panel => {
                        panel.classList.remove('active');
                    });
                    
                    // Show the selected tab panel
                    document.getElementById(tabId).classList.add('active');
                    
                    // Deactivate all tab buttons
                    tabBtns.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    
                    // Activate the clicked tab button
                    this.classList.add('active');
                });
            });
            
            // Star rating
            const ratingStars = document.querySelectorAll('.rating-stars input');
            const ratingLabels = document.querySelectorAll('.rating-stars label');
            
            ratingStars.forEach((star, index) => {
                star.addEventListener('change', function() {
                    const rating = this.value;
                    
                    // Update star icons
                    ratingLabels.forEach((label, i) => {
                        const starIcon = label.querySelector('i');
                        if (i < rating) {
                            starIcon.className = 'fas fa-star';
                        } else {
                            starIcon.className = 'far fa-star';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html> 