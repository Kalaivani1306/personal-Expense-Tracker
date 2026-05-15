<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);
$amount = floatval($data['amount']);

if ($amount <= 0) {
    exit('Invalid amount.');
}

$stmt = $conn->prepare("UPDATE expenses SET amount = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("dii", $amount, $id, $_SESSION['user_id']);
$stmt->execute();

echo "Amount updated successfully.";
?>
