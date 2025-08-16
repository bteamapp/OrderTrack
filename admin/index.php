<?php
require_once __DIR__ . '/includes/header.php';

// Fetch stats
$pending_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Delivered'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'Delivered'")->fetch_assoc()['total'];
$total_products = $conn->query("SELECT COUNT(id) as count FROM products")->fetch_assoc()['count'];
?>

<h2>Dashboard</h2>
<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Pending Orders</h3>
        <p><?= $pending_orders ?></p>
    </div>
    <div class="stat-card">
        <h3>Completed Orders</h3>
        <p><?= $completed_orders ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <p><?= format_price($total_revenue ?? 0) ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Products</h3>
        <p><?= $total_products ?></p>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>