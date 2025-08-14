<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Get logged-in user
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];

if (isset($_GET['id'])) {
    $expense_id = intval($_GET['id']);

    // Fetch expense details
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $expense_id, $user_id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();

    if (!$expense) {
        die("Expense not found or not authorized.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    $update = $conn->prepare("UPDATE expenses SET category=?, amount=?, date=?, description=? WHERE id=? AND user_id=?");
    $update->bind_param("sdssii", $category, $amount, $date, $description, $expense_id, $user_id);
    $update->execute();

    header("Location: view-expenses.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Expense</title>
</head>
<body>
    <h2>Edit Expense</h2>
    <form method="POST">
        <label>Category:</label>
        <input type="text" name="category" value="<?= htmlspecialchars($expense['category']) ?>" required><br>

        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" value="<?= $expense['amount'] ?>" required><br>

        <label>Date:</label>
        <input type="date" name="date" value="<?= $expense['date'] ?>" required><br>

        <label>Description:</label>
        <textarea name="description"><?= htmlspecialchars($expense['description']) ?></textarea><br>

        <button type="submit">Update</button>
    </form>
</body>
</html>