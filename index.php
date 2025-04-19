<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopEasy - Your Online Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <section class="hero">
            <div class="container">
                <h1>Welcome to ShopEasy</h1>
                <p>Find the best products at the best prices</p>
                <a href="shop.php" class="btn btn-primary">Shop Now</a>
            </div>
        </section>

        <section class="featured-products">
            <div class="container">
                <h2>Featured Products</h2>
                <div class="product-grid">
                    <?php
                        require_once 'includes/db.php';
                        
                        $sql = "SELECT * FROM products WHERE featured = 1 LIMIT 4";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<div class="product-card">';
                                echo '<img src="' . $row["image"] . '" alt="' . $row["name"] . '">';
                                echo '<h3>' . $row["name"] . '</h3>';
                                echo '<p class="price">$' . $row["price"] . '</p>';
                                echo '<a href="product.php?id=' . $row["id"] . '" class="btn">View Details</a>';
                                echo '<button class="btn add-to-cart" data-id="' . $row["id"] . '">Add to Cart</button>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>No featured products found</p>";
                        }
                    ?>
                </div>
            </div>
        </section>

        <section class="categories">
            <div class="container">
                <h2>Shop by Category</h2>
                <div class="category-grid">
                    <?php
                        $sql = "SELECT * FROM categories LIMIT 4";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<a href="category.php?id=' . $row["id"] . '" class="category-card">';
                                echo '<img src="' . $row["image"] . '" alt="' . $row["name"] . '">';
                                echo '<h3>' . $row["name"] . '</h3>';
                                echo '</a>';
                            }
                        } else {
                            echo "<p>No categories found</p>";
                        }
                    ?>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html> 