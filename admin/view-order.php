<?php
require_once __DIR__ . '/includes/header.php';

if (!isset($_GET['id'])) {
    redirect('orders.php');
}
$order_id = (int)$_GET['id'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);

    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();

    // Add to history
    $stmt_history = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)");
    $stmt_history->bind_param("iss", $order_id, $new_status, $notes);
    $stmt_history->execute();

    // Redirect to prevent form resubmission
    redirect("view_order.php?id=$order_id&status=updated");
}

// Fetch order details
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch order items
$items_stmt = $conn->prepare("SELECT p.name, p.sku, oi.quantity, oi.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch status history
$history_stmt = $conn->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC");
$history_stmt->bind_param("i", $order_id);
$history_stmt->execute();
$history = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$possible_statuses = ['Pending', 'Processing', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];
?>

<h2>Order Details: <?= htmlspecialchars($order['order_code']) ?></h2>

<?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
    <div class="message success">Order status updated successfully!</div>
<?php endif; ?>

<div class="order-view-grid">
    <div class="order-view-main">
        <h4>Order Items</h4>
        <table class="data-table">
            <thead>
                <tr><th>Product</th><th>SKU</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['sku']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= format_price($item['price']) ?></td>
                    <td><?= format_price($item['price'] * $item['quantity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" class="text-right"><strong>Subtotal</strong></td><td><?= format_price($order['total_amount'] - $order['shipping_fee']) ?></td></tr>
                <tr><td colspan="4" class="text-right"><strong>Shipping Fee</strong></td><td><?= format_price($order['shipping_fee']) ?></td></tr>
                <tr><td colspan="4" class="text-right"><strong>Total</strong></td><td><strong><?= format_price($order['total_amount']) ?></strong></td></tr>
            </tfoot>
        </table>

        <h4>Update Status</h4>
        <form action="" method="post" class="form-inline">
            <select name="status">
                <?php foreach ($possible_statuses as $status): ?>
                    <option value="<?= $status ?>" <?= $order['status'] == $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="notes" placeholder="Optional notes (e.g., Tracking ID)">
            <button type="submit" name="update_status">Update Status</button>
        </form>

        <h4>Order History</h4>
        <ul class="timeline admin-timeline">
            <?php foreach ($history as $history_item): ?>
                <li>
                    <div class="time"><?= date('M j, Y H:i', strtotime($history_item['created_at'])) ?></div>
                    <div class="event"><strong><?= htmlspecialchars($history_item['status']) ?></strong>
                    <?php if (!empty($history_item['notes'])): ?><p><?= htmlspecialchars($history_item['notes']) ?></p><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="order-view-sidebar">
        <h4>Customer Details</h4>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
        <p><strong>Address:</strong><br><?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
        
        <h4>Print</h4>
        <div class="print-buttons">
            <a href="print_invoice.php?id=<?= $order_id ?>" target="_blank" class="button">Print Invoice</a>
            <a href="print_label.php?id=<?= $order_id ?>" target="_blank" class="button">Print Shipping Label</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>