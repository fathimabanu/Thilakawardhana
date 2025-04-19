# ShopEasy - E-Commerce Website

Thialakawardhana is a fully functional e-commerce website which sells all kind of products ranging from electronics to clothing built with HTML, CSS, JavaScript, and PHP. It includes both customer-facing shopping functionality and an admin panel for managing products, categories, users, and orders.

## Features

### Customer Features
- User registration and login system
- Product browsing with filtering and sorting
- Product search functionality
- Product details with images, descriptions, and reviews
- Shopping cart management
- Checkout process
- Order tracking
- User profile and order history

### Admin Features
- Dashboard with sales statistics
- Product management (add, edit, delete)
- Category management
- Order management
- Customer management
- Review moderation
- Coupon management
- Settings configuration

## Installation

1. Set up a web server (Apache/Nginx) with PHP support

2. Import the database schema:
   ```
   mysql -u username -p < database.sql
   ```

3. Configure the database connection in `includes/db.php`:
   ```php
   $servername = "localhost";
   $username = "your_username";
   $password = "your_password";
   $dbname = "shopeasy";
   ```

4. Upload the files to your web server or use a local development environment like XAMPP/WAMP

## Admin Access

- URL: `/admin/index.php`
- Email: admin@shopeasy.com
- Password: password

## Technologies Used

- HTML5
- CSS3
- JavaScript (ES6)
- PHP 7.4+
- MySQL
- Font Awesome 6
- Responsive Design

## Project Structure

- `/admin` - Admin panel files
- `/assets` - CSS, JavaScript, images, and other static assets
- `/includes` - PHP includes for common functionality
- `/uploads` - User-uploaded files (product images, etc.)

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- Font Awesome for the icons
- All contributors who helped with the project 