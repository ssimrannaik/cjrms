<?php
/**
 * Authentication Guard for CJRMS
 * Handles session management and role-based access control
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
}

/**
 * Get current user's role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Get current user's ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's username
 * @return string|null
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user's full name
 * @return string|null
 */
function getCurrentUserFullName() {
    return $_SESSION['full_name'] ?? null;
}

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php?error=login_required');
        exit();
    }
}

/**
 * Require specific role(s)
 * @param string|array $allowedRoles Single role or array of allowed roles
 */
function requireRole($allowedRoles) {
    requireLogin();
    
    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    $currentRole = getCurrentUserRole();
    
    if (!in_array($currentRole, $allowedRoles)) {
        header('Location: index.php?error=access_denied');
        exit();
    }
}

/**
 * Check if current user has specific role
 * @param string $role Role to check
 * @return bool
 */
function hasRole($role) {
    return getCurrentUserRole() === $role;
}

/**
 * Check if current user has any of the specified roles
 * @param array $roles Array of roles to check
 * @return bool
 */
function hasAnyRole($roles) {
    $currentRole = getCurrentUserRole();
    return in_array($currentRole, $roles);
}

/**
 * Login user and set session variables
 * @param array $user User data from database
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check session timeout (optional security feature)
 * @param int $timeout Timeout in seconds (default: 2 hours)
 */
function checkSessionTimeout($timeout = 7200) {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            logoutUser();
            header('Location: login.php?error=session_timeout');
            exit();
        }
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get navigation items based on user role
 * @return array
 */
function getNavigationItems() {
    $role = getCurrentUserRole();
    $items = [];
    
    // Common items for all authenticated users
    if (isLoggedIn()) {
        $items[] = ['url' => 'index.php', 'title' => 'Dashboard', 'icon' => '🏠'];
        $items[] = ['url' => 'search.php', 'title' => 'Search', 'icon' => '🔍'];
    }
    
    // Role-specific items
    switch ($role) {
        case 'Police':
            $items[] = ['url' => 'register_complaint.php', 'title' => 'Register Complaint', 'icon' => '📝'];
            $items[] = ['url' => 'manage_complaints.php', 'title' => 'Manage Complaints', 'icon' => '📋'];
            break;
            
        case 'Court':
            $items[] = ['url' => 'manage_complaints.php', 'title' => 'Review Complaints', 'icon' => '📋'];
            $items[] = ['url' => 'manage_cases.php', 'title' => 'Manage Cases', 'icon' => '⚖️'];
            break;
            
        case 'Admin':
            $items[] = ['url' => 'manage_complaints.php', 'title' => 'All Complaints', 'icon' => '📋'];
            $items[] = ['url' => 'manage_cases.php', 'title' => 'All Cases', 'icon' => '⚖️'];
            $items[] = ['url' => 'manage_admin.php', 'title' => 'Admin Panel', 'icon' => '⚙️'];
            break;
            
        case 'Lawyer':
            $items[] = ['url' => 'manage_cases.php', 'title' => 'My Cases', 'icon' => '⚖️'];
            break;
    }
    
    return $items;
}

/**
 * Display error message if present in URL
 */
function displayErrorMessage() {
    if (isset($_GET['error'])) {
        $error = $_GET['error'];
        $messages = [
            'login_required' => 'Please log in to access this page.',
            'access_denied' => 'You do not have permission to access this page.',
            'session_timeout' => 'Your session has expired. Please log in again.',
            'invalid_credentials' => 'Invalid username or password.',
            'login_failed' => 'Login failed. Please try again.'
        ];
        
        $message = $messages[$error] ?? 'An error occurred.';
        echo '<div class="alert alert-danger">' . sanitizeOutput($message) . '</div>';
    }
}

/**
 * Display success message if present in URL
 */
function displaySuccessMessage() {
    if (isset($_GET['success'])) {
        $success = $_GET['success'];
        $messages = [
            'logout' => 'You have been successfully logged out.',
            'complaint_registered' => 'Complaint has been registered successfully.',
            'case_created' => 'Case has been created successfully.',
            'judgment_recorded' => 'Judgment has been recorded successfully.',
            'data_updated' => 'Data has been updated successfully.'
        ];
        
        $message = $messages[$success] ?? 'Operation completed successfully.';
        echo '<div class="alert alert-success">' . sanitizeOutput($message) . '</div>';
    }
}
?>
