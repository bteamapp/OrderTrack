<?php
require_once __DIR__ . '/includes/header.php';

$products_result = $conn->query("SELECT id, name, price, sku FROM products WHERE stock_quantity > 0 ORDER BY name ASC");
$products = $products_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize customer data
    $customer_name = sanitize($_POST['customer_name']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $customer_address = sanitize($_POST['customer_address']);
    $shipping_fee = (float) $_POST['shipping_fee'];
    $notes = sanitize($_POST['notes']);

    // Process order items
    $order_items = [];
    $total_amount = 0;
    if (isset($_POST['product_id'])) {
        foreach ($_POST['product_id'] as $key => $pid) {
            $qty = (int)$_POST['quantity'][$key];
            if ($qty > 0) {
                // Find product price from our fetched list to prevent price manipulation
                $product_price = 0;
                foreach($products as $p) {
                    if ($p['id'] == $pid) {
                        $product_price = $p['price'];
                        break;
                    }
                }
                $order_items[] = ['id' => $pid, 'quantity' => $qty, 'price' => $product_price];
                $total_amount += $qty * $product_price;
            }
        }
    }
    $total_amount += $shipping_fee;

    if (!empty($order_items)) {
        $conn->begin_transaction();
        try {
            $order_code = generate_order_code();
            // Insert into orders table
            $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_name, customer_phone, customer_address, total_amount, shipping_fee, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("ssssdds", $order_code, $customer_name, $customer_phone, $customer_address, $total_amount, $shipping_fee, $notes);
            $stmt->execute();
            $order_id = $conn->insert_id;

            // Insert into order_items table
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($order_items as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }

            // Insert initial status history
            $history_stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'Pending', 'Order created by admin.')");
            $history_stmt->bind_param("i", $order_id);
            $history_stmt->execute();
            
            $conn->commit();
            redirect("view_order.php?id=$order_id");
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error creating order: " . $e->getMessage();
        }
    }
}
?>
<h2>Create New Order</h2>

<form action="" method="post" id="create-order-form">
    <div class="form-grid">
        <div class="form-section">
            <h4>Customer Information</h4>
            <div class="form-group">
                <label for="customer_name">Full Name</label>
                <input type="text" id="customer_name" name="customer_name" required>
            </div>
            <div class="form-group">
                <label for="customer_phone">Phone Number</label>
                <input type="tel" id="customer_phone" name="customer_phone" required>
            </div>
            <div class="form-group">
                <label for="customer_address">Shipping Address</label>
                <textarea id="customer_address" name="customer_address" rows="4" required></textarea>
            </div>
             <div class="form-group">
                <label for="notes">Order Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
        </div>

        <div class="form-section">
            <h4>Order Items</h4>
            <div class="form-group">
                 <label for="product-selector">Add Product</label>
                 <div class="product-adder">
                    <select id="product-selector">
                        <option value="">-- Select a product --</option>
                        <?php foreach($products as $product): ?>
                            <option value="<?= $product['id'] ?>" data-price="<?= $product['price'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>">
                                <?= htmlspecialchars($product['name']) ?> (<?= format_price($product['price']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="add-product-btn">Add</button>
                 </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr>
                </thead>
                <tbody id="order-items-table">
                    <!-- JS will populate this -->
                </tbody>
            </table>
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal"><?= format_price(0) ?></span>
                </div>
                <div class="summary-row">
                    <label for="shipping_fee">Shipping Fee:</label>
                    <input type="number" id="shipping_fee" name="shipping_fee" value="0.00" step="0.01">
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="grand-total"><?= format_price(0) ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="button button-primary">Create Order</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-product-btn');
    const productSelector = document.getElementById('product-selector');
    const itemsTable = document.getElementById('order-items-table');
    const shippingFeeInput = document.getElementById('shipping_fee');

    addBtn.addEventListener('click', function() {
        const selectedOption = productSelector.options[productSelector.selectedIndex];
        if (!selectedOption.value) return;

        const productId = selectedOption.value;
        const productName = selectedOption.getAttribute('data-name');
        const productPrice = parseFloat(selectedOption.getAttribute('data-price'));
        
        // Prevent adding the same product twice
        if(document.querySelector(`input[name="product_id[]"][value="${productId}"]`)) {
            alert('Product already added.');
            return;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                ${productName}
                <input type="hidden" name="product_id[]" value="${productId}">
            </td>
            <td><input type="number" name="quantity[]" value="1" min="1" class="item-quantity" data-price="${productPrice}"></td>
            <td>${formatCurrency(productPrice)}</td>
            <td class="item-total">${formatCurrency(productPrice)}</td>
            <td><button type="button" class="remove-item-btn">X</button></td>
        `;
        itemsTable.appendChild(row);
        updateTotals();
    });

    itemsTable.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item-btn')) {
            e.target.closest('tr').remove();
            updateTotals();
        }
    });

    itemsTable.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity')) {
            const row = e.target.closest('tr');
            const price = parseFloat(e.target.getAttribute('data-price'));
            const quantity = parseInt(e.target.value) || 0;
            const totalCell = row.querySelector('.item-total');
            totalCell.textContent = formatCurrency(price * quantity);
            updateTotals();
        }
    });
    
    shippingFeeInput.addEventListener('input', updateTotals);

    function updateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-quantity').forEach(input => {
            const price = parseFloat(input.getAttribute('data-price'));
            const quantity = parseInt(input.value) || 0;
            subtotal += price * quantity;
        });
        
        const shipping = parseFloat(shippingFeeInput.value) || 0;
        const grandTotal = subtotal + shipping;

        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
        document.getElementById('grand-total').textContent = formatCurrency(grandTotal);
    }
    
    function formatCurrency(amount) {
        return '<?= DEFAULT_CURRENCY ?>' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>