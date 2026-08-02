<?php
// Shared auth helper. Include this at the top of any protected page or API endpoint.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_username() {
    return $_SESSION['username'] ?? null;
}

// Use on regular pages (index.php): redirects to login if not authenticated.
function require_login_page() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Use on api/*.php endpoints: returns a 401 JSON response if not authenticated.
function require_login_api() {
    if (!is_logged_in()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
        exit;
    }
}
