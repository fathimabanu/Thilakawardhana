<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

// Get statistics
$stats = array();

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();
$stats['products'] = $row['count'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
$row = $result->fetch_assoc();
$stats['orders'] = $row['count'];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
$stats['users'] = $row['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total) as total FROM orders WHERE status = 'completed'");
$row = $result->fetch_assoc();
$stats['revenue'] = $row['total'] ? $row['total'] : 0;

// Recent orders
$result = $conn->query("SELECT o.*, u.first_name, u.last_name FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = array();
while ($row = $result->fetch_assoc()) {
    $recent_orders[] = $row;
}

// Low stock products
$result = $conn->query("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 5");
$low_stock = array();
while ($row = $result->fetch_assoc()) {
    $low_stock[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ShopEasy</title>
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
                    <h1>Dashboard</h1>
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
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-card-icon blue">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-card-info">
                            <h2><?php echo $stats['products']; ?></h2>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-icon green">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-card-info">
                            <h2><?php echo $stats['orders']; ?></h2>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-icon orange">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-card-info">
                            <h2><?php echo $stats['users']; ?></h2>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-icon red">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-card-info">
                            <h2>$<?php echo number_format($stats['revenue'], 2); ?></h2>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-grid">
                    <div class="dashboard-card recent-orders">
                        <div class="card-header">
                            <h2>Recent Orders</h2>
                            <a href="orders.php" class="view-all">View All</a>
                        </div>
                        <div class="card-content">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">No orders found</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>$<?php echo number_format($order['total'], 2); ?></td>
                                            <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                            <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="action-btn"><i class="fas fa-eye"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="dashboard-card low-stock">
                        <div class="card-header">
                            <h2>Low Stock Products</h2>
                            <a href="products.php?filter=low_stock" class="view-all">View All</a>
                        </div>
                        <div class="card-content">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($low_stock)): ?>
                                    <tr>
                                        <td colspan="4" class="no-data">No low stock products</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($low_stock as $product): ?>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                                    <span><?php echo $product['name']; ?></span>
                                                </div>
                                            </td>
                                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                                            <td><span class="stock-badge <?php echo $product['stock'] == 0 ? 'out' : 'low'; ?>"><?php echo $product['stock']; ?></span></td>
                                            <td><a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn"><i class="fas fa-edit"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
        });
    </script>
</body>
</html> 