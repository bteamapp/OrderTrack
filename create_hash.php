<?php
// Use a strong password. 'password123' is just for this example.
$passwordToHash = 'password123';

$hash = password_hash($passwordToHash, PASSWORD_DEFAULT);

echo "Your password is: " . $passwordToHash . "<br>";
echo "Your new hash is: " . $hash;
?>