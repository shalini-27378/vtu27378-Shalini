<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get total expenses
$query = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$total_expenses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get monthly expenses
$query = "SELECT COALESCE(SUM(amount), 0) as monthly FROM expenses 
          WHERE user_id = :user_id 
          AND MONTH(expense_date) = MONTH(CURRENT_DATE())
          AND YEAR(expense_date) = YEAR(CURRENT_DATE())";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$monthly_expenses = $stmt->fetch(PDO::FETCH_ASSOC)['monthly'];

// Get category count
$query = "SELECT COUNT(*) as count FROM categories WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$category_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent expenses
$query = "SELECT e.*, c.category_name 
          FROM expenses e 
          LEFT JOIN categories c ON e.category_id = c.category_id 
          WHERE e.user_id = :user_id 
          ORDER BY e.expense_date DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$recent_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <h1>Dashboard</h1>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Expenses</h3>
            <div class="stat-number" data-stat="total">₹<?php echo number_format($total_expenses, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>This Month</h3>
            <div class="stat-number" data-stat="monthly">₹<?php echo number_format($monthly_expenses, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>Categories</h3>
            <div class="stat-number" data-stat="categories"><?php echo $category_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Average Monthly</h3>
            <div class="stat-number" data-stat="average">₹<?php echo $category_count > 0 ? number_format($total_expenses / max(1, $category_count), 2) : '0.00'; ?></div>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-box">
            <h3>Expense Trend</h3>
            <canvas id="expenseChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Category Distribution</h3>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <div class="recent-expenses">
        <h3>Recent Expenses</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_expenses as $expense): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($expense['expense_date'])); ?></td>
                        <td><?php echo htmlspecialchars($expense['category_name'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo htmlspecialchars($expense['description'] ?? '-'); ?></td>
                        <td>₹<?php echo number_format($expense['amount'], 2); ?></td>
                        <td><?php echo ucfirst($expense['payment_method']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Pass PHP data to JavaScript
const dashboardData = {
    stats: {
        total: <?php echo $total_expenses; ?>,
        monthly: <?php echo $monthly_expenses; ?>,
        categories: <?php echo $category_count; ?>
    }
};
</script>

<?php require_once 'includes/footer.php'; ?>