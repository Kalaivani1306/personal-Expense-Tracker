<!DOCTYPE html>
<html>
<head>
    <title>Daily Expense Tracker</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f7f9;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            font-size: 24px;
            text-align: center;
        }

        nav {
            background-color: #333;
            overflow: hidden;
            text-align: center;
        }

        nav a {
            display: inline-block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }

        nav a:hover {
            background-color: #575757;
        }

        .container {
            padding: 30px;
            max-width: 900px;
            margin: auto;
        }
    </style>
</head>
<body>

<header>
    Daily Expense Tracker
</header>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="add_expense.php">Add Expense</a>
    <a href="budget.php">Set Budget</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <!-- Your page content goes here -->
</div>

</body>
</html>
