<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Get logged-in user_id
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];
$userQuery->close();

// Default categories (order preserved)
$defaultCategories = ['Food', 'Travel', 'Education', 'Entertainment', 'Shopping'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['category'] === "Others" && !empty($_POST['new_category'])) {
        $category = trim($_POST['new_category']);
    } else {
        $category = $_POST['category'];
    }

    // Check if category already exists for this user
    $checkCat = $conn->prepare("SELECT 1 FROM categories WHERE user_id = ? AND category_name = ?");
    $checkCat->bind_param("is", $user_id, $category);
    $checkCat->execute();
    $checkCat->store_result();

    if ($checkCat->num_rows === 0) {
        $insertCat = $conn->prepare("INSERT INTO categories (user_id, category_name) VALUES (?, ?)");
        $insertCat->bind_param("is", $user_id, $category);
        $insertCat->execute();
        $insertCat->close();
    }
    $checkCat->close();

    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    $sql = "INSERT INTO expenses (user_id, category, amount, date, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss", $user_id, $category, $amount, $date, $description);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid resubmission
    header("Location: add-expense.php?success=1");
    exit();
}

// Fetch categories from DB for this user
$catQuery = $conn->prepare("SELECT category_name FROM categories WHERE user_id = ?");
$catQuery->bind_param("i", $user_id);
$catQuery->execute();
$catResult = $catQuery->get_result();
$userCategories = [];
while ($row = $catResult->fetch_assoc()) {
    $userCategories[] = $row['category_name'];
}
$catQuery->close();

// Merge defaults + user categories without duplicates
$categories = array_unique(array_merge($defaultCategories, $userCategories), SORT_REGULAR);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            display: flex;
            background: 
                linear-gradient(rgba(0,0,0,.45), rgba(0,0,0,.45)),
                url('https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=1920&q=80')
                no-repeat center center / cover;
            background-attachment: fixed;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #15a6abff, #15a6abff);
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

    .sidebar h3 {
        text-align: center;
        margin-bottom: 30px;
        font-weight: 600;
        font-size: 20px;
        color: black; /* Menu title in black */
    }

    .sidebar a {
            display: block;
            padding: 14px 20px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 15px;
            font-family: 'Poppins', sans-serif; /* Changed font style */
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }

    .sidebar a:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 4px solid #ffdd59;
        padding-left: 24px;
    }

        /* Main content */
        .main-content {
            margin-left: 240px;
            padding: 40px;
            flex-grow: 1;
            color: #fff;
        }

        h2 {
            margin-top: 0;
        }

        form {
            max-width: 500px;
            background: rgba(0,0,0,0.4);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.4);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
            min-height: 60px;
        }

        button {
            background: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            border: none;
        }

        button:hover {
            background: #218838;
        }

        p {
            font-size: 14px;
        }
    </style>
    <script>
        function toggleNewCategoryField() {
            var categorySelect = document.getElementById("category");
            var newCategoryDiv = document.getElementById("newCategoryDiv");
            newCategoryDiv.style.display = (categorySelect.value === "Others") ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h3>Menu</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="add-expense.php">Add Expense</a>
        <a href="view-expenses.php">View Expenses</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content">
        <h2>Add Expense</h2>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: lightgreen;">Expense added successfully!</p>
        <?php endif; ?>
        <form method="post">
            <label>Category:</label>
            <select id="category" name="category" required onchange="toggleNewCategoryField()">
                <option value="" disabled selected>-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
                <option value="Others">Others</option>
            </select>

            <div id="newCategoryDiv" style="display:none; margin-top:10px;">
                <label>New Category:</label>
                <input type="text" name="new_category" placeholder="Enter new category">
            </div>

            <label>Amount:</label>
            <input type="number" step="0.01" name="amount" required>

            <label>Date:</label>
            <input type="date" name="date" required>

            <label>Note (Optional):</label>
            <textarea name="description"></textarea>

            <button type="submit">Add Expense</button>
        </form>
    </div>
</body>
</html>
