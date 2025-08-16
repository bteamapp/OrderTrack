<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['id'])) die("No order specified.");
$order_id = (int)$_GET['id'];
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
if (!$order) die("Order not found.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shipping Label - <?= $order['order_code'] ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/print.css">
</head>
<body>
    <div class="shipping-label">
        <div class="from-address">
            <strong>FROM:</strong><br>
            <?= SITE_NAME ?><br>
            123 Your Street<br>
            Your City, 12345
        </div>
        <hr>
        <div class="to-address">
            <strong>TO:</strong><br>
            <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
            <?= nl2br(htmlspecialchars($order['customer_address'])) ?><br>
            <strong><?= htmlspecialchars($order['customer_phone']) ?></strong>
        </div>
        <hr>
        <div class="order-info">
            <strong>Order ID:</strong> <?= $order['order_code'] ?>
        </div>
    </div>
     <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>