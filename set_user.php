<?php
require 'database.php';
session_start();

// Debugging: Check if data is received
if (!isset($_POST["user_id"]) || empty($_POST["user_id"])) {
    header("Location: select_user.php?error=no_user_selected");
    exit;
}

// Sanitize user input
$userId = intval($_POST["user_id"]);

// Check if user exists in DB
$query = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$query->execute([$userId]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: select_user.php?error=user_not_found");
    exit;
}

// Store user ID in session
$_SESSION["user_id"] = $userId;

// Debugging: Ensure session is set
if (!isset($_SESSION["user_id"])) {
    header("Location: select_user.php?error=session_not_set");
    exit;
}

// Redirect to game
header("Location: index.php");
exit;
?>
