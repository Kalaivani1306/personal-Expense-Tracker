<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// TEMP: Simulate login for testing if no login system yet
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // assuming test user ID is 1
}

include 'db.php';

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Get current month
$current_month = date('Y-m');

// Get budget for the month
$budget_result = $conn->query("SELECT * FROM budgets WHERE user_id = $user_id AND month_year = '$current_month'");
$budget = $budget_result->fetch_assoc();

// Get expenses
$expense_result = $conn->query("SELECT * FROM expenses WHERE user_id = $user_id ORDER BY date DESC");

$total_expenses = 0;
$expenses = [];

while ($row = $expense_result->fetch_assoc()) {
    $total_expenses += $row['amount'];
    $expenses[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daily Expense Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        header {
            background-color: #2196F3;
            color: white;
            padding: 15px;
        }
        nav a {
            margin-right: 20px;
            color: white;
            text-decoration: none;
        }
        .budget-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f1f1f1;
        }
        .warning {
            color: red;
            font-weight: bold;
        }
        table {
            margin-top: 15px;
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #bbb;
        }
        th, td {
            padding: 8px;
        }
        .delete-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>

<header>
    <h2>Welcome, <?php echo htmlspecialchars($user['username'] ?? 'Guest'); ?></h2>
    <nav>
        <a href="add_expense.php">Add Expense</a>
        <a href="budget.php">Set Budget</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="budget-section">
    <h3>Monthly Budget - <?php echo $current_month; ?></h3>
    <?php if ($budget): ?>
        <p><strong>Budget:</strong> ₹<?php echo $budget['amount']; ?></p>
        <p><strong>Total Expenses:</strong> ₹<?php echo $total_expenses; ?></p>
        <?php if ($total_expenses > $budget['amount']): ?>
            <p class="warning">⚠ You've exceeded your monthly budget!</p>
        <?php else: ?>
            <p>✅ You're within budget.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>No budget set for this month. <a href="budget.php">Set one now</a>.</p>
    <?php endif; ?>
</div>

<h3>Your Expenses</h3>
<?php if (count($expenses) > 0): ?>
    <table>
        <tr>
            <th>Title</th>
            <th>Amount (₹)</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php foreach ($expenses as $expense): ?>
        <tr>
            <td><?php echo htmlspecialchars($expense['category'] ?? ''); ?></td>
            <td><?php echo $expense['amount']; ?></td>
            <td><?php echo $expense['expense_date']; ?></td>
            <td><button class="delete-btn" onclick="deleteExpense(<?php echo $expense['id']; ?>)">Delete</button></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No expenses yet.</p>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
    <script>alert("✅ Expense added successfully!");</script>
<?php endif; ?>

<!-- DELETE EXPENSE SCRIPT -->
<script>
function deleteExpense(id) {
    if (confirm("Are you sure you want to delete this expense?")) {
        fetch('delete_expense.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        });
    }
}
</script>

</body>
</html>
