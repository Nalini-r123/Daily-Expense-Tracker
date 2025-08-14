<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Get logged in user ID
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// Get expense ID from URL
if (isset($_GET['id'])) {
    $expense_id = intval($_GET['id']);

    // Delete only if the expense belongs to this user
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $expense_id, $user_id);
    $stmt->execute();

    header("Location: view-expenses.php");
    exit();
} else {
    echo "Invalid request.";
}
?>