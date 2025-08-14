<?php
session_start();
require 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>
            alert('Invalid password!');
            window.location.href = 'index.html';
        </script>";
    }
} else {
    echo "<script>
        alert('User not found!');
        window.location.href = 'index.html';
    </script>";
}
?>