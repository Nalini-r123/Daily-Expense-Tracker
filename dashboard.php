<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

require 'db.php';

// Fetch the logged-in user's ID
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'];
$userQuery->close();

// Calculate current month's total
$currentMonth = date('Y-m');
$totalQuery = $conn->prepare("
    SELECT SUM(amount) AS total
    FROM expenses
    WHERE user_id = ?
      AND DATE_FORMAT(date, '%Y-%m') = ?
");
$totalQuery->bind_param("is", $user_id, $currentMonth);
$totalQuery->execute();
$result = $totalQuery->get_result();
$data = $result->fetch_assoc();
$currentMonthTotal = $data['total'] ?? 0;
$totalQuery->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Daily Expense Tracker</title>
    <link rel="stylesheet" href="style.css">
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
            background: #15a6abff; 
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }   
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 20px;
            letter-spacing: 1px;
            color: black; /* Changed to black */
        }
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

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
            background-color: rgba(0, 0, 0, 0.05);
            border-left: 4px solid #ffdd59;
            padding-left: 24px;
            font-family: 'Times New Roman', serif; /* Changed font on hover */
        }
        /* Main Content */
        .main-content {
            margin-left: 240px;
            padding: 20px;
            flex-grow: 1;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Align to top */
            text-align: center;
        }

        /* Stat Card */
        .stat-card {
            background: linear-gradient(135deg, #15a6abff, #15a6abff);
            padding: 20px;
            border-radius: 12px;
            color: white;
            width: 250px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
            margin-top: 10px;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 18px;
        }
        .stat-card p {
            font-size: 28px;
            margin: 10px 0 0;
            font-weight: bold;
        }

        /* Chatbot Styles */
        #chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0078ff, #00b4d8);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 1000;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        #chat-toggle:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        }
        #chat-container {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 340px;
            height: 460px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            background: white;
            display: flex;
            flex-direction: column;
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        #chat-container.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        #chat-box {
            flex: 1;
            padding: 14px;
            overflow-y: auto;
            font-size: 14px;
            background: #f5f7fa;
        }
        #chat-box div {
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            max-width: 80%;
            line-height: 1.4;
            word-wrap: break-word;
        }
        .user {
            background: #2695b4ff;
            color: white;
            margin-left: auto;
        }
        .bot {
            background: #e5e5ea;
            color: black;
            margin-right: auto;
            white-space: pre-wrap;
        }
        #chat-input {
            display: flex;
            border-top: 1px solid #ddd;
            background: white;
        }
        #message {
            flex: 1;
            padding: 12px;
            border: none;
            outline: none;
            font-size: 14px;
        }
        #send {
            padding: 12px 16px;
            background: #0078ff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        #send:hover {
            background: #005fcc;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Menu</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="add-expense.php">Add Expense</a>
        <a href="view-expenses.php">View Expenses</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="stat-card">
            <h3>This Month's Total</h3>
            <p>₹<?php echo number_format($currentMonthTotal, 2); ?></p>
        </div>
    </div>

    <!-- Floating Chat Icon -->
    <div id="chat-toggle">💬</div>

    <!-- Chatbot -->
    <div id="chat-container">
        <div id="chat-box"></div>
        <div id="chat-input">
            <input type="text" id="message" placeholder="Type a message...">
            <button id="send">Send</button>
        </div>
    </div>

    <script>
        const API_KEY = "YOUR_GOOGLE_API_KEY_HERE";
        const MODEL = "gemini-1.5-flash";

        const toggleBtn = document.getElementById("chat-toggle");
        const chatContainer = document.getElementById("chat-container");

        toggleBtn.addEventListener("click", () => {
            chatContainer.classList.toggle("show");
        });

        async function getGeminiResponse(userMessage) {
            const response = await fetch(
                `https://generativelanguage.googleapis.com/v1beta/models/${MODEL}:generateContent?key=${API_KEY}`,
                {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        contents: [{ role: "user", parts: [{ text: userMessage }] }]
                    })
                }
            );
            const data = await response.json();
            return data.candidates?.[0]?.content?.parts?.[0]?.text || "No response.";
        }

        async function sendMessage() {
            const messageInput = document.getElementById("message");
            const chatBox = document.getElementById("chat-box");
            const userMessage = messageInput.value.trim();
            if (!userMessage) return;

            chatBox.innerHTML += `<div class="user">${userMessage}</div>`;
            messageInput.value = "";

            const botReply = await getGeminiResponse(userMessage);
            chatBox.innerHTML += `<div class="bot">${botReply}</div>`;
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        document.getElementById("send").addEventListener("click", sendMessage);
        document.getElementById("message").addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                sendMessage();
            }
        });
    </script>
</body>
</html>
