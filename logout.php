<?php
/**
 * Logout Handler for CJRMS
 * Destroys session and redirects to login
 */
require_once 'auth_guard.php';

// Logout user
logoutUser();

// Redirect to login page with success message
header('Location: login.php?success=logout');
exit();
?>
