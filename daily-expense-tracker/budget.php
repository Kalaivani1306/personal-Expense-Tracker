<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$user_id = $_SESSION['user_id'];
$month = date('Y-m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];

    $check = $conn->query("SELECT * FROM budgets WHERE user_id = $user_id AND month_year = '$month'");

    if ($check->num_rows > 0) {
        $conn->query("UPDATE budgets SET amount = $amount WHERE user_id = $user_id AND month_year = '$month'");
    } else {
        $conn->query("INSERT INTO budgets (user_id, amount, month_year) VALUES ($user_id, $amount, '$month')");
    }

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set Monthly Budget</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-weight: bold;
        }

        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        p {
            text-align: center;
            margin-top: 15px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <form method="post">
        <h2>Set Monthly Budget (<?php echo $month; ?>)</h2>
        <label>Amount (₹):</label><br>
        <input type="number" step="0.01" name="amount" required><br>
        <button type="submit">Save Budget</button>
        <p><a href="index.php">Back to Home</a></p>
    </form>

</body>
</html>
