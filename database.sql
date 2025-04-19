-- Create database
CREATE DATABASE IF NOT EXISTS shopeasy;
USE shopeasy;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    remember_token VARCHAR(100),
    token_expires DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    parent_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    full_description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2),
    image VARCHAR(255),
    sku VARCHAR(50),
    stock INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    category_id INT,
    specifications JSON,
    tags VARCHAR(255),
    rating DECIMAL(3, 1) DEFAULT 0,
    reviews_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Create product_images table
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT,
    rating INT NOT NULL,
    review TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) DEFAULT 0,
    shipping DECIMAL(10, 2) DEFAULT 0,
    discount DECIMAL(10, 2) DEFAULT 0,
    coupon_code VARCHAR(50),
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    shipping_name VARCHAR(100),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_state VARCHAR(100),
    shipping_zipcode VARCHAR(20),
    shipping_country VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_email VARCHAR(100),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Create coupons table
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    value DECIMAL(10, 2) NOT NULL,
    min_order_value DECIMAL(10, 2) DEFAULT 0,
    max_discount_value DECIMAL(10, 2),
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    limit_per_user INT,
    times_used INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert admin user
INSERT INTO users (first_name, last_name, email, phone, password, role)
VALUES ('Admin', 'User', 'admin@shopeasy.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert some categories
INSERT INTO categories (name, description, image)
VALUES 
('Electronics', 'All electronic devices and gadgets', 'assets/img/categories/electronics.jpg'),
('Clothing', 'Men, women, and children clothing', 'assets/img/categories/clothing.jpg'),
('Home & Kitchen', 'Everything for your home', 'assets/img/categories/home-kitchen.jpg'),
('Books', 'Fiction, non-fiction, and textbooks', 'assets/img/categories/books.jpg');

-- Insert some subcategories
INSERT INTO categories (name, description, image, parent_id)
VALUES 
('Smartphones', 'Latest smartphones from top brands', 'assets/img/categories/smartphones.jpg', 1),
('Laptops', 'Powerful laptops for work and play', 'assets/img/categories/laptops.jpg', 1),
('Men''s Clothing', 'Clothes for men', 'assets/img/categories/mens-clothing.jpg', 2),
('Women''s Clothing', 'Clothes for women', 'assets/img/categories/womens-clothing.jpg', 2);

-- Insert some products
INSERT INTO products (name, description, price, image, stock, featured, category_id, sku, tags)
VALUES 
('Smartphone X', 'Latest smartphone with advanced features', 899.99, 'assets/img/products/smartphone-x.jpg', 50, TRUE, 5, 'SP-X-001', 'smartphone,electronics,mobile'),
('Smartphone Y', 'Budget-friendly smartphone with great camera', 499.99, 'assets/img/products/smartphone-y.jpg', 75, FALSE, 5, 'SP-Y-001', 'smartphone,electronics,mobile'),
('Laptop Pro', 'Professional laptop for developers', 1299.99, 'assets/img/products/laptop-pro.jpg', 30, TRUE, 6, 'LP-001', 'laptop,electronics,computer'),
('Laptop Air', 'Thin and light laptop for students', 899.99, 'assets/img/products/laptop-air.jpg', 45, FALSE, 6, 'LA-001', 'laptop,electronics,computer'),
('Men''s T-Shirt', 'Comfortable cotton t-shirt for men', 29.99, 'assets/img/products/mens-tshirt.jpg', 100, FALSE, 7, 'MT-001', 'clothing,t-shirt,men'),
('Men''s Jeans', 'Classic blue jeans for men', 59.99, 'assets/img/products/mens-jeans.jpg', 80, TRUE, 7, 'MJ-001', 'clothing,jeans,men'),
('Women''s Dress', 'Elegant dress for women', 79.99, 'assets/img/products/womens-dress.jpg', 60, TRUE, 8, 'WD-001', 'clothing,dress,women'),
('Women''s Blouse', 'Stylish blouse for women', 49.99, 'assets/img/products/womens-blouse.jpg', 70, FALSE, 8, 'WB-001', 'clothing,blouse,women');

-- Insert product images
INSERT INTO product_images (product_id, image, sort_order)
VALUES 
(1, 'assets/img/products/smartphone-x.jpg', 1),
(1, 'assets/img/products/smartphone-x-2.jpg', 2),
(1, 'assets/img/products/smartphone-x-3.jpg', 3),
(2, 'assets/img/products/smartphone-y.jpg', 1),
(2, 'assets/img/products/smartphone-y-2.jpg', 2),
(3, 'assets/img/products/laptop-pro.jpg', 1),
(3, 'assets/img/products/laptop-pro-2.jpg', 2),
(4, 'assets/img/products/laptop-air.jpg', 1),
(5, 'assets/img/products/mens-tshirt.jpg', 1),
(6, 'assets/img/products/mens-jeans.jpg', 1),
(7, 'assets/img/products/womens-dress.jpg', 1),
(8, 'assets/img/products/womens-blouse.jpg', 1);

-- Insert some reviews
INSERT INTO reviews (product_id, user_id, rating, review, status)
VALUES 
(1, 1, 5, 'Excellent smartphone! The camera is amazing.', 'approved'),
(1, 1, 4, 'Great phone, but battery life could be better.', 'approved'),
(3, 1, 5, 'Perfect laptop for coding and development.', 'approved'),
(6, 1, 4, 'Good quality jeans, fits well.', 'approved'),
(7, 1, 5, 'Beautiful dress, received many compliments.', 'approved');

-- Update product ratings based on reviews
UPDATE products p
SET rating = (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND status = 'approved'),
    reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved')
WHERE id IN (1, 3, 6, 7);

-- Insert some settings
INSERT INTO settings (setting_key, setting_value)
VALUES 
('site_name', 'ShopEasy'),
('site_description', 'Your one-stop shop for all your needs'),
('currency', 'USD'),
('tax_rate', '7.5'),
('shipping_rate', '10'),
('free_shipping_threshold', '100'),
('contact_email', 'contact@shopeasy.com'),
('contact_phone', '+1 234 567 890'),
('contact_address', '123 Main Street, City, Country'); 