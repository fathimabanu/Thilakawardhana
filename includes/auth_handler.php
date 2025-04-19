<?php
session_start();
require_once 'db.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'login':
            handleLogin();
            break;
            
        case 'register':
            handleRegistration();
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        default:
            $_SESSION['error'] = 'Invalid action';
            header('Location: ../login.php');
            exit;
    }
} else {
    // Redirect to login page if not a POST request
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../login.php');
    exit;
}

// Handle user login
function handleLogin() {
    global $conn;
    
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please enter both email and password';
        header('Location: ../login.php');
        exit;
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set remember me cookie if checked
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Store token in database
                $stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expires = ? WHERE id = ?");
                $stmt->bind_param("ssi", $token, date('Y-m-d H:i:s', $expires), $user['id']);
                $stmt->execute();
                
                // Set cookie
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            }
            
            // Redirect based on user role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: ../login.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: ../login.php');
        exit;
    }
    
    $stmt->close();
}

// Handle user registration
function handleRegistration() {
    global $conn;
    
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Please fill in all required fields';
        header('Location: ../register.php');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address';
        header('Location: ../register.php');
        exit;
    }
    
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long';
        header('Location: ../register.php');
        exit;
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ../register.php');
        exit;
    }
    
    if (!$terms) {
        $_SESSION['error'] = 'You must agree to the Terms and Conditions';
        header('Location: ../register.php');
        exit;
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email address is already registered';
        header('Location: ../register.php');
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, ?, 'customer', NOW())");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Registration successful! You can now login';
        header('Location: ../login.php');
        exit;
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again';
        header('Location: ../register.php');
        exit;
    }
    
    $stmt->close();
}

// Handle user logout
function handleLogout() {
    // Clear session
    session_unset();
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Redirect to login page
    header('Location: ../login.php');
    exit;
}
?> 