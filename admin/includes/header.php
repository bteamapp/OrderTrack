<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in()) {
    redirect(SITE_URL . '/admin/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <h2><?= SITE_NAME ?></h2>
            <nav>
                <ul>
                    <li><a href="<?= SITE_URL ?>/admin/index.php">Dashboard</a></li>
                    <li><a href="<?= SITE_URL ?>/admin/orders.php">Orders</a></li>
                    <li><a href="<?= SITE_URL ?>/admin/create_order.php">Create Order</a></li>
                    <li><a href="<?= SITE_URL ?>/admin/products.php">Products</a></li>
                    <li><a href="<?= SITE_URL ?>/admin/users.php">User Management</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="<?= SITE_URL ?>/admin/profile.php">My Profile</a>
                <a href="<?= SITE_URL ?>/admin/logout.php">Logout</a>
            </div>
        </aside>
        <main class="main-content">
            <header class="admin-header">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
            </header>
            <div class="content">