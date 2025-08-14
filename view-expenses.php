<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Get the logged-in user's ID
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];
$userQuery->close();

// Fetch only this user's expenses, newest first
$result = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC");
$result->bind_param("i", $user_id);
$result->execute();
$expenses = $result->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Expenses</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard">
    <div class="sidebar">
        <h3>Menu</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="add-expense.php">Add Expense</a>
        <a href="view-expenses.php">View Expenses</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>All Expenses</h2>
        <table border="1">
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Note</th>
            </tr>
            <?php while ($row = $expenses->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= $row['date'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <button><a href="edit-expense.php?id=<?= $row['id'] ?>">Edit</a></button> | 
                        <button><a href="delete-expense.php?id=<?= $row['id'] ?>" 
                        onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a></button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>