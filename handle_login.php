<?php
/**
 * Login Handler for CJRMS
 * Processes login form submission
 */
require_once 'auth_guard.php';
require_once 'db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: login.php?error=invalid_request');
    exit();
}

// Get and sanitize input
$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    header('Location: login.php?error=invalid_credentials');
    exit();
}

try {
    // Query user from database
    $sql = "SELECT user_id, username, password_hash, role, full_name 
            FROM Users 
            WHERE username = ? AND user_id IS NOT NULL";
    
    $user = fetchRow($sql, [$username]);
    
    // Check if user exists and password is correct
    if ($user && password_verify($password, $user['password_hash'])) {
        // Login successful - set session
        loginUser($user);
        
        // Redirect to dashboard
        header('Location: index.php?success=login');
        exit();
    } else {
        // Login failed
        header('Location: login.php?error=invalid_credentials');
        exit();
    }
    
} catch (Exception $e) {
    // Log error and redirect
    error_log("Login error: " . $e->getMessage());
    header('Location: login.php?error=login_failed');
    exit();
}
?>
