<?php
require_once __DIR__ . '/includes/header.php';

// Basic search
$search_query = "";
$where_clause = "";
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = $conn->real_escape_string($_GET['q']);
    $where_clause = " WHERE order_code LIKE '%$search_term%' OR customer_name LIKE '%$search_term%' OR customer_phone LIKE '%$search_term%'";
}

$orders_result = $conn->query("SELECT * FROM orders" . $where_clause . " ORDER BY created_at DESC");
?>

<h2>Manage Orders</h2>

<div class="toolbar">
    <form action="" method="get" class="search-form">
        <input type="text" name="q" placeholder="Search by Order ID, Name, Phone..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
        <button type="submit">Search</button>
    </form>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($orders_result->num_rows > 0): ?>
            <?php while($order = $orders_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($order['order_code']) ?></td>
                    <td>
                        <?= htmlspecialchars($order['customer_name']) ?><br>
                        <small><?= htmlspecialchars($order['customer_phone']) ?></small>
                    </td>
                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    <td><?= format_price($order['total_amount']) ?></td>
                    <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                    <td>
                        <a href="view_order.php?id=<?= $order['id'] ?>" class="button button-small">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No orders found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>