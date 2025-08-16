<?php
require_once __DIR__ . '/includes/header.php';

// Handle Add/Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($user_id > 0) { // Update
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }
    } else { // Insert
        if(empty($password)) {
            $error = "Password is required for new users.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
        }
    }
    
    if (isset($stmt) && $stmt->execute()) {
        redirect('users.php?status=success');
    } else {
        $error_msg = isset($error) ? $error : "Error: " . $stmt->error;
    }
}

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];
    // Prevent user from deleting themselves
    if ($id_to_delete == $_SESSION['user_id']) {
        redirect('users.php?status=self_delete_error');
    } else {
        $conn->query("DELETE FROM users WHERE id = $id_to_delete");
        redirect('users.php?status=deleted');
    }
}

$users_result = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY name ASC");

$edit_user = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_to_edit = (int)$_GET['id'];
    $edit_result = $conn->query("SELECT id, name, email FROM users WHERE id = $id_to_edit");
    if ($edit_result->num_rows > 0) {
        $edit_user = $edit_result->fetch_assoc();
    }
}
?>

<h2>User Management</h2>

<?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="message success">User saved successfully!</div>
<?php elseif(isset($_GET['status']) && $_GET['status'] == 'self_delete_error'): ?>
    <div class="message error">You cannot delete your own account.</div>
<?php elseif(isset($error_msg)): ?>
     <div class="message error"><?= $error_msg ?></div>
<?php endif; ?>


<div class="content-split">
    <div class="form-container">
        <h3><?= $edit_user ? 'Edit User' : 'Add New User' ?></h3>
        <form action="users.php" method="post">
            <?php if ($edit_user): ?>
                <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?= $edit_user['name'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= $edit_user['email'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" <?= !$edit_user ? 'required' : '' ?>>
                <?php if ($edit_user): ?><small>Leave blank to keep current password.</small><?php endif; ?>
            </div>
            <button type="submit" name="save_user" class="button"><?= $edit_user ? 'Update User' : 'Add User' ?></button>
            <?php if($edit_user): ?>
                <a href="users.php" class="button button-secondary">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <h3>Existing Users</h3>
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php while($user = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                    <td>
                        <a href="users.php?action=edit&id=<?= $user['id'] ?>" class="button button-small">Edit</a>
                        <a href="users.php?action=delete&id=<?= $user['id'] ?>" class="button button-small button-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>