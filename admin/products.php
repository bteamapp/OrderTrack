<?php
require_once __DIR__ . '/includes/header.php';

// Handle Add/Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $sku = sanitize($_POST['sku']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock_quantity'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($product_id > 0) { // Update
        $stmt = $conn->prepare("UPDATE products SET name = ?, sku = ?, price = ?, stock_quantity = ? WHERE id = ?");
        $stmt->bind_param("ssddi", $name, $sku, $price, $stock, $product_id);
    } else { // Insert
        $stmt = $conn->prepare("INSERT INTO products (name, sku, price, stock_quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $name, $sku, $price, $stock);
    }
    
    if ($stmt->execute()) {
        redirect('products.php?status=success');
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle Delete Product
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];
    $conn->query("DELETE FROM products WHERE id = $id_to_delete");
    redirect('products.php?status=deleted');
}

// Fetch products
$products_result = $conn->query("SELECT * FROM products ORDER BY name ASC");

// Fetch product for editing if ID is provided
$edit_product = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_to_edit = (int)$_GET['id'];
    $edit_result = $conn->query("SELECT * FROM products WHERE id = $id_to_edit");
    if ($edit_result->num_rows > 0) {
        $edit_product = $edit_result->fetch_assoc();
    }
}
?>

<h2>Manage Products</h2>

<?php if(isset($_GET['status'])): ?>
    <div class="message success">Product saved successfully!</div>
<?php endif; ?>

<div class="content-split">
    <div class="form-container">
        <h3><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h3>
        <form action="products.php" method="post">
            <?php if ($edit_product): ?>
                <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?= $edit_product['name'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="sku">SKU</label>
                <input type="text" id="sku" name="sku" value="<?= $edit_product['sku'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" value="<?= $edit_product['price'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="<?= $edit_product['stock_quantity'] ?? '' ?>" required>
            </div>
            <button type="submit" class="button"><?= $edit_product ? 'Update Product' : 'Add Product' ?></button>
            <?php if($edit_product): ?>
                <a href="products.php" class="button button-secondary">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <h3>Existing Products</h3>
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if ($products_result->num_rows > 0): ?>
                <?php while($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['sku']) ?></td>
                        <td><?= format_price($product['price']) ?></td>
                        <td><?= $product['stock_quantity'] ?></td>
                        <td>
                            <a href="products.php?action=edit&id=<?= $product['id'] ?>" class="button button-small">Edit</a>
                            <a href="products.php?action=delete&id=<?= $product['id'] ?>" class="button button-small button-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No products found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>