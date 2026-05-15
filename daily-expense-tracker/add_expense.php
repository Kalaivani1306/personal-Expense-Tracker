<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle new expense submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'])) {
    header("Content-Type: application/json");

    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    if (!empty($amount) && !empty($category) && !empty($date)) {
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, amount, category, expense_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $amount, $category, $date);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "✅ Expense added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "❌ Error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "warning", "message" => "⚠ Please fill all fields."]);
    }
    exit(); // important to stop further rendering
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; margin: 20px; background-color: #f2f6ff; }
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
<div class="top-bar">
    <button onclick="window.location.href='index.php'">🏠 Home</button>
</div>


<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form id="addExpenseForm"  method="POST">
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    <input type="text" name="category" placeholder="Enter category" required>
    <input type="date" name="date" required>
    <button type="submit">➕ Add Expense</button>
</form>

<div class="top-bar">
    <button onclick="clearCurrentMonth()">🧹 Clear Current Month</button>
    <select id="monthSelect" onchange="fetchExpenses()">
        <option value="current">Current Month</option>
    </select>
</div>

<table id="expenseTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Amount (click to edit)</th>
            <th>Category</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <!-- Expenses will be loaded dynamically -->
    </tbody>
</table>
<script>
window.onload = function() {
    loadMonths();
    fetchExpenses();

    // ✅ This ensures the DOM is loaded before attaching the form listener
    document.getElementById('addExpenseForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch('', {
            method: 'POST',
            body: formData
        }).then(res => res.text())
          .then(() => {
              this.reset();        // Clear form
              fetchExpenses();     // Refresh expenses list
          });
    });
};

function loadMonths() {
    fetch('fetch_expenses.php?loadMonths=1')
    .then(response => response.json())
    .then(data => {
        let select = document.getElementById('monthSelect');
        data.months.forEach(month => {
            let option = document.createElement('option');
            option.value = month;
            option.text = month;
            select.appendChild(option);
        });
    });
}

function fetchExpenses() {
    let month = document.getElementById('monthSelect').value;
    fetch('fetch_expenses.php?month=' + month)
    .then(response => response.json())
    .then(data => {
        let tbody = document.querySelector('#expenseTable tbody');
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4">No expenses found.</td></tr>';
        } else {
            data.forEach(expense => {
                let row = `<tr>
                    <td>${expense.id}</td>
                    <td contenteditable="true" onblur="updateAmount(${expense.id}, this)">${expense.amount}</td>
                    <td>${expense.category}</td>
                    <td>${expense.expense_date}</td>
                </tr>`;
                tbody.innerHTML += row;
            });
        }
    });
}

function updateAmount(id, element) {
    let newAmount = element.innerText.trim();
    if (newAmount === '' || isNaN(newAmount) || Number(newAmount) <= 0) {
        alert('Please enter a valid amount.');
        fetchExpenses();
        return;
    }
    fetch('update_expense.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, amount: newAmount })
    }).then(response => response.text())
      .then(data => console.log(data));
}

function clearCurrentMonth() {
    if (confirm("Are you sure you want to clear current month expenses?")) {
        fetch('clear_expenses.php', { method: 'POST' })
        .then(response => response.text())
        .then(data => {
            alert(data);
            fetchExpenses();
        });
    }
}
</script>


</body>
</html>