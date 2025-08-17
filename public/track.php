<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

$order = null;
$error = '';
$search_term = '';

// Check for GET request (from QR code)
if (isset($_GET['code'])) {
    $search_term = sanitize($_GET['code']);
}

// Check for POST request (from form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_term'])) {
    $search_term = sanitize($_POST['search_term']);
}

if (!empty($search_term)) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ? OR customer_phone = ?");
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Fetch status history
        $history_stmt = $conn->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC");
        $history_stmt->bind_param("i", $order['id']);
        $history_stmt->execute();
        $order['history'] = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "No order found with the provided details. Please check and try again.";
    }
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
     $error = "Please enter an Order ID or Phone Number.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="container public-track">
        <h1>Track Your Order</h1>
        <p>Enter your Order ID or the Phone Number you used during checkout to see your order details and status.</p>
        
        <form action="<?= SITE_URL ?>/" method="post" class="track-form">
            <input type="text" name="search_term" placeholder="Enter Order ID or Phone Number" value="<?= htmlspecialchars($search_term) ?>" required>
            <button type="submit">Track Order</button>
        </form>

        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <!-- The rest of the HTML is the same as before -->
            <div class="order-details">
                <h2>Order Details</h2>
                <div class="detail-grid">
                    <div><strong>Order ID:</strong> <?= htmlspecialchars($order['order_code']) ?></div>
                    <div><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                    <div><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></div>
                    <div><strong>Status:</strong> <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= htmlspecialchars($order['status']) ?></span></div>
                </div>

                <h3>Order History</h3>
                <ul class="timeline">
                    <?php if (!empty($order['history'])): ?>
                        <?php foreach ($order['history'] as $history_item): ?>
                            <li>
                                <div class="time"><?= date('M j, Y H:i', strtotime($history_item['created_at'])) ?></div>
                                <div class="event">
                                    <strong><?= htmlspecialchars($history_item['status']) ?></strong>
                                    <?php if (!empty($history_item['notes'])): ?>
                                        <p><?= htmlspecialchars($history_item['notes']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No history available yet.</li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
         <div class="footer-link">
            <a href="<?= SITE_URL ?>/admin">Admin Login</a>
        </div>
    </div>
</body>
</html>