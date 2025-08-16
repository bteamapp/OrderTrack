<?php
// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect to a specific page
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Sanitize user input
function sanitize($input) {
    global $conn;
    return htmlspecialchars(strip_tags(mysqli_real_escape_string($conn, $input)));
}

// Format currency
function format_price($price) {
    return DEFAULT_CURRENCY . number_format($price, 2);
}

// Generate a unique order code
function generate_order_code() {
    return 'ORD-' . strtoupper(substr(uniqid(), 7, 13));
}
?>