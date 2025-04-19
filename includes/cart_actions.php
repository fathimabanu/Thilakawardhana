<?php
session_start();
require_once 'db.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0,
    'cart_total' => 0
];

// Check if cart exists in session, if not create it
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get the action from POST request
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'add':
        // Add item to cart
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if ($product_id > 0) {
            // Get product details from database
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                
                // Check if product is already in cart
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product_id,
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'quantity' => $quantity
                    ];
                }
                
                $response['success'] = true;
                $response['message'] = 'Product added to cart';
            } else {
                $response['message'] = 'Product not found';
            }
            
            $stmt->close();
        } else {
            $response['message'] = 'Invalid product ID';
        }
        break;
        
    case 'update':
        // Update item quantity
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                $response['success'] = true;
                $response['message'] = 'Cart updated';
            } else {
                // Remove item if quantity is 0 or negative
                unset($_SESSION['cart'][$product_id]);
                $response['success'] = true;
                $response['message'] = 'Item removed from cart';
            }
        } else {
            $response['message'] = 'Product not found in cart';
        }
        break;
        
    case 'remove':
        // Remove item from cart
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
        } else {
            $response['message'] = 'Product not found in cart';
        }
        break;
        
    case 'clear':
        // Clear entire cart
        $_SESSION['cart'] = [];
        $response['success'] = true;
        $response['message'] = 'Cart cleared';
        break;
        
    default:
        $response['message'] = 'Invalid action';
}

// Calculate cart totals
$cart_count = 0;
$cart_total = 0;

foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
    $cart_total += $item['price'] * $item['quantity'];
}

$response['cart_count'] = $cart_count;
$response['cart_total'] = number_format($cart_total, 2, '.', '');

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 