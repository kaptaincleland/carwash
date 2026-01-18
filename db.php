<?php
$host = "localhost";
$user = "root";
$pass = ""; // Default XAMPP password is empty
$dbname = "carwashdb";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>