<?php
require_once 'config.php';

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'track';

switch ($url) {
    case 'track':
        require 'public/track.php';
        break;
    case 'admin':
    case 'admin/dashboard':
        require 'admin/index.php';
        break;
    case 'admin/login':
        require 'admin/login.php';
        break;
    // Add other admin routes if needed
    default:
        http_response_code(404);
        echo "404 Page Not Found";
        break;
}
?>