<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "tracker_bud"; // Make sure this DB exists in phpMyAdmin

$conn = new mysqli($servername, $username, $password, $database);

// Show error if connection fails
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
