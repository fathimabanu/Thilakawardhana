<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

// Handle product deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product deleted successfully';
    } else {
        $_SESSION['error'] = 'Error deleting product';
    }
    
    header('Location: products.php');
    exit;
}

// Get filter and search parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build the query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1";

$params = array();
$param_types = '';

// Apply filters
if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'ss';
}

if (!empty($filter)) {
    switch ($filter) {
        case 'low_stock':
            $query .= " AND p.stock <= 5";
            break;
        case 'out_of_stock':
            $query .= " AND p.stock = 0";
            break;
        case 'featured':
            $query .= " AND p.featured = 1";
            break;
        case 'on_sale':
            $query .= " AND p.sale_price > 0 AND p.sale_price < p.price";
            break;
    }
}

if ($category > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
    $param_types .= 'i';
}

// Add sorting
$query .= " ORDER BY p.id DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get all categories for the filter dropdown
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = array();
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin Dashboard - ShopEasy</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-content">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Products</h1>
                </div>
                <div class="admin-header-right">
                    <div class="admin-user">
                        <span><?php echo $_SESSION['user_name']; ?></span>
                        <img src="../assets/img/admin-avatar.jpg" alt="Admin">
                        <div class="admin-dropdown">
                            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="admin-main">
                <?php
                // Display success or error messages
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']);
                }
                
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                
                <div class="admin-actions">
                    <div class="actions-left">
                        <a href="add-product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
                    </div>
                    <div class="actions-right">
                        <form action="" method="GET" class="search-form">
                            <div class="form-group">
                                <select name="filter" id="filter">
                                    <option value="">All Products</option>
                                    <option value="low_stock" <?php echo $filter == 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                                    <option value="out_of_stock" <?php echo $filter == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="featured" <?php echo $filter == 'featured' ? 'selected' : ''; ?>>Featured</option>
                                    <option value="on_sale" <?php echo $filter == 'on_sale' ? 'selected' : ''; ?>>On Sale</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <select name="category" id="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group search-input-group">
                                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($product = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-thumbnail">
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category_name']; ?></td>
                                    <td>
                                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                        <span class="old-price">$<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="sale-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                        <?php else: ?>
                                        $<?php echo number_format($product['price'], 2); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="stock-badge <?php echo $product['stock'] == 0 ? 'out' : ($product['stock'] <= 5 ? 'low' : 'in'); ?>"><?php echo $product['stock']; ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $product['featured'] ? 'green' : 'gray'; ?>">
                                            <?php echo $product['featured'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">No products found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const adminContainer = document.querySelector('.admin-container');
            
            sidebarToggle.addEventListener('click', function() {
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            // User dropdown
            const adminUser = document.querySelector('.admin-user');
            
            adminUser.addEventListener('click', function() {
                this.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!adminUser.contains(event.target)) {
                    adminUser.classList.remove('active');
                }
            });
            
            // Auto-submit form on select change
            const filterSelect = document.getElementById('filter');
            const categorySelect = document.getElementById('category');
            
            filterSelect.addEventListener('change', function() {
                this.form.submit();
            });
            
            categorySelect.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html> 