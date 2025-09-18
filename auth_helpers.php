<?php
// auth_helpers.php - Improved version with better error handling

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require authentication for a page
 * Redirects to login page if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        // Store the requested URL for redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Check if headers already sent
        if (headers_sent($filename, $linenum)) {
            // If headers already sent, use JavaScript redirect as fallback
            echo '<script>window.location.href = "login_required.php";</script>';
            exit();
        } else {
            // Use PHP header redirect
            header('Location: login_required.php');
            exit();
        }
    }
}

/**
 * Get user information if logged in
 * @return array|null User info or null if not logged in
 */
function getUserInfo() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['name'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}
?>