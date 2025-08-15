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
    <style>
         body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            background:
                linear-gradient(rgba(0,0,0,.45), rgba(0,0,0,.45)),
                url('https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=1920&q=80')
                no-repeat center center / cover;
            display: flex;
        }

        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #15a6abff, #15a6abff);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 20px;
            letter-spacing: 1px;
            color: black;
        }

        .sidebar a {
            display: block;
            padding: 14px 20px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 15px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-family: Arial, sans-serif;
        }

        .sidebar a:hover {
            background-color: rgba(0, 0, 0, 0.05);
            border-left: 4px solid #ffdd59;
            padding-left: 24px;
            font-family: 'Times New Roman', serif;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 240px;
        }

        h2 {
            margin-top: 0;
            font-size: 28px;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #15a6abff;
            color: white;
            font-size: 16px;
        }

        tr:hover {
            background-color: rgba(0,0,0,0.05);
        }

        button a {
            text-decoration: none;
            color: white;
        }

        button {
            background-color: #15a6abff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #118f94;
        }
    </style>
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
        <table>
            <tr>
                <th>Category</th>
                <th>Amount (₹)</th>
                <th>Date</th>
                <th>Note</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $expenses->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td>₹<?= number_format($row['amount'], 2) ?></td>
                    <td><?= $row['date'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <button><a href="edit-expense.php?id=<?= $row['id'] ?>">Edit</a></button>
                        <button><a href="delete-expense.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a></button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>
