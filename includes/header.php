<?php
session_start();
?>
<header>
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <a href="index.php">
                    <h1>ShopEasy</h1>
                </a>
            </div>
            <div class="search-bar">
                <form action="search.php" method="GET">
                    <input type="text" name="query" placeholder="Search products...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="user-actions">
                <div class="cart">
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                </div>
                <div class="account">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="account.php"><i class="fas fa-user"></i> My Account</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <?php
                    require_once 'db.php';
                    $sql = "SELECT * FROM categories LIMIT 5";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<li><a href="category.php?id=' . $row["id"] . '">' . $row["name"] . '</a></li>';
                        }
                    }
                ?>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </div>
</header> 