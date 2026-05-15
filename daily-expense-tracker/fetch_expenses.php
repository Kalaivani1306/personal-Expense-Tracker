<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];

// Load available months for the dropdown
if (isset($_GET['loadMonths'])) {
    $result = $conn->query("SELECT DISTINCT DATE_FORMAT(expense_date, '%Y-%m') AS month FROM expenses WHERE user_id = $user_id ORDER BY expense_date DESC");
    $months = [];
    while ($row = $result->fetch_assoc()) {
        $months[] = $row['month'];
    }
    echo json_encode(['months' => $months]);
    exit();
}

// Handle fetching expenses for selected or current month
$month = $_GET['month'] ?? 'current';

if ($month === "current") {
    $year = date('Y');
    $monthNum = date('m');
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? AND MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
    $stmt->bind_param("iii", $user_id, $monthNum, $year);
} else {
    $parts = explode('-', $month);
    $year = intval($parts[0]);
    $monthNum = intval($parts[1]);
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? AND MONTH(expense_date) = ? AND YEAR(expense_date) = ?");
    $stmt->bind_param("iii", $user_id, $monthNum, $year);
}

$stmt->execute();
$result = $stmt->get_result();
$expenses = [];

while ($row = $result->fetch_assoc()) {
    $expenses[] = $row;
}

echo json_encode($expenses);
?>
