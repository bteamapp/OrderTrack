
<?php
require_once __DIR__ . '/includes/header.php';

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch user's current hashed password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $hashed_password = $result['password'];

    if (password_verify($current_password, $hashed_password)) {
        if (!empty($new_password) && $new_password === $confirm_password) {
            if(strlen($new_password) < 8){
                $error_msg = "New password must be at least 8 characters long.";
            } else {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                if ($update_stmt->execute()) {
                    $success_msg = "Password updated successfully!";
                } else {
                    $error_msg = "Failed to update password. Please try again.";
                }
            }
        } else {
            $error_msg = "New passwords do not match or are empty.";
        }
    } else {
        $error_msg = "Your current password is incorrect.";
    }
}
?>

<h2>My Profile</h2>

<?php if ($success_msg): ?>
    <div class="message success"><?= $success_msg ?></div>
<?php elseif ($error_msg): ?>
    <div class="message error"><?= $error_msg ?></div>
<?php endif; ?>

<div class="form-container" style="max-width: 500px;">
    <h3>Change Password</h3>
    <form action="profile.php" method="post">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" name="change_password" class="button">Change Password</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>