<?php
require_once 'config/database.php';

// Protect all pages except login and register
$public_pages = ['login.php', 'register.php'];
if (!in_array(basename($_SERVER['PHP_SELF']), $public_pages)) {
    requireLogin();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackWise - Expense Analytics Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">TrackWise</div>
            <ul class="nav-links">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="add_expense.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_expense.php' ? 'active' : ''; ?>">Add Expense</a></li>
                <li><a href="view_expenses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'view_expenses.php' ? 'active' : ''; ?>">View Expenses</a></li>
                <li><a href="analytics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">Analytics</a></li>
                <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Categories</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="logout.php" style="color: #ef4444;">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">