<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$month = date('m');
$year = date('Y');

$stmt = $conn->prepare("DELETE FROM expenses WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?");
$stmt->bind_param("iii", $_SESSION['user_id'], $month, $year);
$stmt->execute();

echo "Current month expenses cleared.";
?>