<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "❌ Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents("php://input"), true);
$expense_id = $data['id'] ?? null;

if ($expense_id) {
    // Delete the expense
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $expense_id, $user_id);
    if ($stmt->execute()) {
        echo "✅ Expense deleted successfully.";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "❌ Invalid request.";
}
?>
