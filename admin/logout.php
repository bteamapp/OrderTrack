<?php
require_once __DIR__ . '/../config.php';
session_destroy();
header("Location: " . SITE_URL . "/admin/login.php");
exit();
?>