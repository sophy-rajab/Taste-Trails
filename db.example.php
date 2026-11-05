<?php
// Copy this file to db.php and put your own details below
$host = "localhost";
$user = "your_mysql_username";
$password = "your_mysql_password";
$database = "taste_trail";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>