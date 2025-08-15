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

        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            height: 80px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Expense</h2>
        <form method="POST">
            <label>Category:</label>
            <input type="text" name="category" value="<?= htmlspecialchars($expense['category']) ?>" required>

            <label>Amount:</label>
            <input type="number" step="0.01" name="amount" value="<?= $expense['amount'] ?>" required>

            <label>Date:</label>
            <input type="date" name="date" value="<?= $expense['date'] ?>" required>

            <label>Description:</label>
            <textarea name="description"><?= htmlspecialchars($expense['description']) ?></textarea>

            <button type="submit">Update Expense</button>
        </form>
        <a href="view-expenses.php">Back to Expenses</a>
    </div>
</body>
</html>
