<?php
session_start();
include 'db.php';

// Check if user is logged in (use session for testing purposes)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Simulating a logged-in user for testing
}

$user_id = $_SESSION['user_id'];

// Handle adding an expense
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'], $_POST['category'], $_POST['date'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    if (!empty($amount) && !empty($category) && !empty($date)) {
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, amount, category, date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $amount, $category, $date);
        if ($stmt->execute()) {
            $message = "✅ Expense added successfully.";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ Please fill all fields.";
    }
}

// Fetch all distinct months for the user's expenses
$months = [];
$result = $conn->query("SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month FROM expenses WHERE user_id = $user_id ORDER BY date DESC");
while ($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
}

// Handle fetching expenses for a specific month
$month = isset($_GET['month']) ? $_GET['month'] : 'current'; // Default to 'current' if no month is selected
$expenses = [];

if ($month == "current") {
    $year = date('Y');
    $monthNum = date('m');
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?");
    $stmt->bind_param("iii", $user_id, $monthNum, $year);
} else {
    $parts = explode('-', $month);
    $year = intval($parts[0]);
    $monthNum = intval($parts[1]);
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?");
    $stmt->bind_param("iii", $user_id, $monthNum, $year);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $expenses[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f2f6ff; }
        h2 { color: #333; }
        .top-bar, form { margin-bottom: 20px; }
        .top-bar button, .top-bar select, form input, form button {
            padding: 10px; font-size: 16px; margin-right: 10px; border-radius: 5px; border: 1px solid #ccc;
        }
        .top-bar button, form button { background-color: #007BFF; color: white; border: none; cursor: pointer; }
        .top-bar button:hover { background-color: #0056b3; }
        table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #007BFF; color: white; }
        td[contenteditable="true"] { background-color: #eef2ff; cursor: pointer; }
        .message { color: green; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>

<h2>🧾 Daily Expense Tracker</h2>

<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<!-- Form to Add an Expense -->
<form method="POST">
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    <input type="text" name="category" placeholder="Category" required>
    <input type="date" name="date" required>
    <button type="submit">➕ Add Expense</button>
</form>

<div class="top-bar">
    <!-- Dropdown for selecting month -->
    <select id="monthSelect" onchange="window.location.href = '?month=' + this.value;">
        <option value="current" <?php echo ($month == 'current') ? 'selected' : ''; ?>>Current Month</option>
        <?php foreach ($months as $m): ?>
            <option value="<?= $m ?>" <?php echo ($month == $m) ? 'selected' : ''; ?>><?= $m ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Display Expenses -->
<table id="expenseTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Amount (₹)</th>
            <th>Category</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($expenses) > 0): ?>
            <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?= $expense['id'] ?></td>
                    <td><?= $expense['amount'] ?></td>
                    <td><?= htmlspecialchars($expense['category']) ?></td>
                    <td><?= $expense['date'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No expenses found for this month.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>

<?php
// DB connection (replace with your actual connection details)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "expense_tracker";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
