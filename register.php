<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Generate random password
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$&*_?:.'), 0, 8);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
            alert('User registered successfully!\\nYour generated password is: $password\\nPlease note it down or take a screenshot.');
            window.location.href = 'index.html';
        </script>";
    } else {
        echo "<script>
            alert('Error: $conn->error');
            window.location.href = 'register.html';
        </script>";
    }
}
?>