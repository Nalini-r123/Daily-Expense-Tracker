db.php
<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "expenses_tracker";
// $port = use portno. if not set to default

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>