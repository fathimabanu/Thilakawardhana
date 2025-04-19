<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - ShopEasy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <section class="shop-header">
            <div class="container">
                <h1>Shop All Products</h1>
                <div class="breadcrumb">
                    <a href="index.php">Home</a> / <span>Shop</span>
                </div>
            </div>
        </section>

        <section class="shop-content">
            <div class="container">
                <div class="shop-grid">
                    <div class="sidebar">
                        <div class="filter-section">
                            <h3>Categories</h3>
                            <ul class="category-list">
                                <?php
                                    require_once 'includes/db.php';
                                    
                                    $sql = "SELECT * FROM categories ORDER BY name";
                                    $result = $conn->query($sql);
                                    
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo '<li><a href="category.php?id=' . $row["id"] . '">' . $row["name"] . '</a></li>';
                                        }
                                    } else {
                                        echo "<li>No categories found</li>";
                                    }
                                ?>
                            </ul>
                        </div>
                        
                        <div class="filter-section">
                            <h3>Price Range</h3>
                            <form action="shop.php" method="GET" class="price-filter">
                                <div class="price-inputs">
                                    <input type="number" name="min_price" placeholder="Min" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                                    <span>-</span>
                                    <input type="number" name="max_price" placeholder="Max" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
                                </div>
                                <button type="submit" class="btn">Apply</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="products">
                        <div class="products-header">
                            <div class="products-count">
                                <?php
                                    // Build the SQL query with filters
                                    $sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
                                    
                                    // Price filter
                                    if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                                        $min_price = floatval($_GET['min_price']);
                                        $sql .= " AND price >= $min_price";
                                    }
                                    
                                    if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                                        $max_price = floatval($_GET['max_price']);
                                        $sql .= " AND price <= $max_price";
                                    }
                                    
                                    // Category filter
                                    if (isset($_GET['category']) && !empty($_GET['category'])) {
                                        $category = intval($_GET['category']);
                                        $sql .= " AND category_id = $category";
                                    }
                                    
                                    $result = $conn->query($sql);
                                    $row = $result->fetch_assoc();
                                    $total_products = $row['total'];
                                    
                                    echo "<p>Showing {$total_products} products</p>";
                                ?>
                            </div>
                            <div class="sort-options">
                                <label for="sort">Sort by:</label>
                                <select name="sort" id="sort" onchange="window.location.href=this.value">
                                    <option value="shop.php?sort=newest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest</option>
                                    <option value="shop.php?sort=price_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="shop.php?sort=price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="shop.php?sort=name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                                    <option value="shop.php?sort=name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name: Z to A</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="product-grid">
                            <?php
                                // Pagination
                                $items_per_page = 12;
                                $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                                $offset = ($current_page - 1) * $items_per_page;
                                
                                // Build the SQL query with filters
                                $sql = "SELECT * FROM products WHERE 1=1";
                                
                                // Price filter
                                if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                                    $min_price = floatval($_GET['min_price']);
                                    $sql .= " AND price >= $min_price";
                                }
                                
                                if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                                    $max_price = floatval($_GET['max_price']);
                                    $sql .= " AND price <= $max_price";
                                }
                                
                                // Category filter
                                if (isset($_GET['category']) && !empty($_GET['category'])) {
                                    $category = intval($_GET['category']);
                                    $sql .= " AND category_id = $category";
                                }
                                
                                // Sorting
                                if (isset($_GET['sort'])) {
                                    switch ($_GET['sort']) {
                                        case 'newest':
                                            $sql .= " ORDER BY created_at DESC";
                                            break;
                                        case 'price_low':
                                            $sql .= " ORDER BY price ASC";
                                            break;
                                        case 'price_high':
                                            $sql .= " ORDER BY price DESC";
                                            break;
                                        case 'name_asc':
                                            $sql .= " ORDER BY name ASC";
                                            break;
                                        case 'name_desc':
                                            $sql .= " ORDER BY name DESC";
                                            break;
                                        default:
                                            $sql .= " ORDER BY created_at DESC";
                                    }
                                } else {
                                    $sql .= " ORDER BY created_at DESC";
                                }
                                
                                $sql .= " LIMIT $items_per_page OFFSET $offset";
                                
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
                                    echo "<p class='no-products'>No products found matching your criteria</p>";
                                }
                            ?>
                        </div>
                        
                        <div class="pagination">
                            <?php
                                // Calculate total pages
                                $total_pages = ceil($total_products / $items_per_page);
                                
                                if ($total_pages > 1) {
                                    echo '<ul>';
                                    
                                    // Previous page link
                                    if ($current_page > 1) {
                                        echo '<li><a href="shop.php?page=' . ($current_page - 1) . '"><i class="fas fa-chevron-left"></i></a></li>';
                                    }
                                    
                                    // Page numbers
                                    for ($i = 1; $i <= $total_pages; $i++) {
                                        if ($i == $current_page) {
                                            echo '<li class="active"><span>' . $i . '</span></li>';
                                        } else {
                                            echo '<li><a href="shop.php?page=' . $i . '">' . $i . '</a></li>';
                                        }
                                    }
                                    
                                    // Next page link
                                    if ($current_page < $total_pages) {
                                        echo '<li><a href="shop.php?page=' . ($current_page + 1) . '"><i class="fas fa-chevron-right"></i></a></li>';
                                    }
                                    
                                    echo '</ul>';
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html> 