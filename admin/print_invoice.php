<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
// Include the QR code library
require_once __DIR__ . '/../libs/phpqrcode/qrlib.php';

if (!isset($_GET['id'])) die("No order specified.");
$order_id = (int)$_GET['id'];

// Fetch data
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$items = $conn->query("SELECT p.name, p.sku, oi.quantity, oi.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id")->fetch_all(MYSQLI_ASSOC);
if (!$order) die("Order not found.");

// --- QR Code Generation ---
$track_url = SITE_URL . '/track.php?code=' . $order['order_code'];
$qr_code_file = __DIR__ . '/../temp/qr_' . $order['order_code'] . '.png';
QRcode::png($track_url, $qr_code_file, QR_ECLEVEL_L, 4);

$qr_code_base64 = base64_encode(file_get_contents($qr_code_file));
unlink($qr_code_file); // Clean up the temp file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - <?= $order['order_code'] ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/print.css">
</head>
<body>
    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <h1>Invoice</h1>
                <strong><?= SITE_NAME ?></strong><br>
                123 Your Street<br>
                Your City, 12345
            </div>
            <div class="qr-code">
                <img src="data:image/png;base64,<?= $qr_code_base64 ?>" alt="Track Order QR Code">
                <p>Scan to Track</p>
            </div>
        </div>
        
        <div class="header-meta">
            <div>
                <strong>Invoice #:</strong> <?= $order['order_code'] ?><br>
                <strong>Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?>
            </div>
        </div>
        <hr>
        <div class="address">
            <strong>Bill To:</strong><br>
            <?= htmlspecialchars($order['customer_name']) ?><br>
            <?= nl2br(htmlspecialchars($order['customer_address'])) ?><br>
            <?= htmlspecialchars($order['customer_phone']) ?>
        </div>
        <table>
            <!-- Table content remains the same -->
             <thead>
                <tr><th>Item</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= format_price($item['price']) ?></td>
                    <td><?= format_price($item['price'] * $item['quantity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="3">Subtotal</td><td><?= format_price($order['total_amount'] - $order['shipping_fee']) ?></td></tr>
                <tr><td colspan="3">Shipping</td><td><?= format_price($order['shipping_fee']) ?></td></tr>
                <tr class="total"><td colspan="3">Total</td><td><?= format_price($order['total_amount']) ?></td></tr>
            </tfoot>
        </table>
        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>