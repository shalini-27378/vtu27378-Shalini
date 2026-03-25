<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get date range filter (default: current month)
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Handle custom date range
if (isset($_GET['apply_range'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Get predefined ranges
$range = $_GET['range'] ?? 'month';

switch($range) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        break;
    case 'quarter':
        $start_date = date('Y-m-01', strtotime('-3 months'));
        $end_date = date('Y-m-d');
        break;
    case 'year':
        $start_date = date('Y-01-01');
        $end_date = date('Y-12-31');
        break;
}

// Summary statistics
$query = "SELECT 
            COUNT(*) as total_transactions,
            COALESCE(SUM(amount), 0) as total_amount,
            COALESCE(AVG(amount), 0) as avg_amount,
            COALESCE(MAX(amount), 0) as max_amount,
            COALESCE(MIN(amount), 0) as min_amount
          FROM expenses 
          WHERE user_id = :user_id 
          AND expense_date BETWEEN :start_date AND :end_date";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Monthly trend data
$query = "SELECT 
            DATE_FORMAT(expense_date, '%Y-%m') as month,
            DATE_FORMAT(expense_date, '%M %Y') as month_name,
            SUM(amount) as total,
            COUNT(*) as transaction_count,
            AVG(amount) as avg_amount
          FROM expenses 
          WHERE user_id = :user_id 
          AND expense_date BETWEEN :start_date AND :end_date
          GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
          ORDER BY month ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Category wise expenses
$query = "SELECT 
            c.category_name,
            c.category_type,
            COALESCE(SUM(e.amount), 0) as total,
            COUNT(e.expense_id) as transaction_count,
            COALESCE(AVG(e.amount), 0) as avg_amount
          FROM categories c
          LEFT JOIN expenses e ON c.category_id = e.category_id 
            AND e.expense_date BETWEEN :start_date AND :end_date
          WHERE c.user_id = :user_id AND c.category_type = 'expense'
          GROUP BY c.category_id, c.category_name
          HAVING total > 0
          ORDER BY total DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$category_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Payment method distribution
$query = "SELECT 
            payment_method,
            COUNT(*) as count,
            SUM(amount) as total,
            AVG(amount) as avg_amount
          FROM expenses 
          WHERE user_id = :user_id 
          AND expense_date BETWEEN :start_date AND :end_date
          GROUP BY payment_method";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$payment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Daily expenses for the selected period
$query = "SELECT 
            expense_date,
            SUM(amount) as daily_total,
            COUNT(*) as transaction_count
          FROM expenses 
          WHERE user_id = :user_id 
          AND expense_date BETWEEN :start_date AND :end_date
          GROUP BY expense_date
          ORDER BY expense_date DESC
          LIMIT 30";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top 5 expenses
$query = "SELECT 
            e.*,
            c.category_name
          FROM expenses e
          LEFT JOIN categories c ON e.category_id = c.category_id
          WHERE e.user_id = :user_id 
          AND e.expense_date BETWEEN :start_date AND :end_date
          ORDER BY e.amount DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$top_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for charts
$chart_labels = [];
$chart_data = [];
$chart_colors = [
    '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#14b8a6', '#f97316', '#6b7280', '#84cc16'
];

foreach ($monthly_data as $month) {
    $chart_labels[] = $month['month_name'];
    $chart_data[] = $month['total'];
}

$category_labels = [];
$category_totals = [];
foreach ($category_data as $category) {
    $category_labels[] = $category['category_name'];
    $category_totals[] = $category['total'];
}
?>

<div class="main-content">
    <h1>Expense Analytics</h1>
    
    <!-- Date Range Filter -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; border-radius: 15px; margin-bottom: 2rem; color: white;">
        <h3 style="color: white; margin-bottom: 1rem;">Select Time Period</h3>
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="color: white; display: block; margin-bottom: 0.5rem;">Quick Range</label>
                <select name="range" class="form-control" onchange="this.form.submit()">
                    <option value="week" <?php echo $range == 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="month" <?php echo $range == 'month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="quarter" <?php echo $range == 'quarter' ? 'selected' : ''; ?>>Last 3 Months</option>
                    <option value="year" <?php echo $range == 'year' ? 'selected' : ''; ?>>This Year</option>
                    <option value="custom" <?php echo isset($_GET['apply_range']) ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>
            
            <div style="flex: 1;">
                <label style="color: white; display: block; margin-bottom: 0.5rem;">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            
            <div style="flex: 1;">
                <label style="color: white; display: block; margin-bottom: 0.5rem;">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            
            <div>
                <button type="submit" name="apply_range" value="1" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="dashboard-stats">
        <div class="stat-card" style="background: linear-gradient(135deg, #4f46e5, #818cf8);">
            <h3>Total Expenses</h3>
            <div class="stat-number">₹<?php echo number_format($summary['total_amount'], 2); ?></div>
            <small><?php echo $summary['total_transactions']; ?> transactions</small>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #34d399);">
            <h3>Average Transaction</h3>
            <div class="stat-number">₹<?php echo number_format($summary['avg_amount'], 2); ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
            <h3>Highest Expense</h3>
            <div class="stat-number">₹<?php echo number_format($summary['max_amount'], 2); ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #f87171);">
            <h3>Lowest Expense</h3>
            <div class="stat-number">₹<?php echo number_format($summary['min_amount'], 2); ?></div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="charts-container">
        <div class="chart-box">
            <h3>Monthly Expense Trend</h3>
            <canvas id="trendChart"></canvas>
        </div>
        
        <div class="chart-box">
            <h3>Expense by Category</h3>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
    
    <!-- Second Row of Charts -->
    <div class="charts-container">
        <div class="chart-box">
            <h3>Payment Method Distribution</h3>
            <canvas id="paymentChart"></canvas>
        </div>
        
        <div class="chart-box">
            <h3>Daily Expenses (Last 30 Days)</h3>
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
    
    <!-- Category Breakdown Table -->
    <div style="margin-top: 2rem;">
        <h3>Category Breakdown</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Transactions</th>
                        <th>Total Amount</th>
                        <th>Average per Transaction</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = array_sum($category_totals);
                    foreach ($category_data as $category): 
                        $percentage = $grand_total > 0 ? round(($category['total'] / $grand_total) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                        <td><?php echo $category['transaction_count']; ?></td>
                        <td>₹<?php echo number_format($category['total'], 2); ?></td>
                        <td>₹<?php echo number_format($category['avg_amount'], 2); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 100px; height: 10px; background: #e5e7eb; border-radius: 5px;">
                                    <div style="width: <?php echo $percentage; ?>%; height: 100%; background: <?php echo $chart_colors[array_rand($chart_colors)]; ?>; border-radius: 5px;"></div>
                                </div>
                                <span><?php echo $percentage; ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Expenses Table -->
    <div style="margin-top: 2rem;">
        <h3>Top 5 Highest Expenses</h3>
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
                    <?php foreach ($top_expenses as $expense): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($expense['expense_date'])); ?></td>
                        <td><?php echo htmlspecialchars($expense['category_name'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo htmlspecialchars($expense['description'] ?? '-'); ?></td>
                        <td><strong>₹<?php echo number_format($expense['amount'], 2); ?></strong></td>
                        <td><?php echo ucfirst($expense['payment_method']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Monthly Expenses',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value;
                        }
                    }
                }
            }
        }
    });
    
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($category_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($category_totals); ?>,
                backgroundColor: <?php echo json_encode(array_slice($chart_colors, 0, count($category_labels))); ?>,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value / total) * 100);
                            return label + ': ₹' + value.toFixed(2) + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    
    // Payment Method Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($payment_data, 'payment_method')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($payment_data, 'total')); ?>,
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Daily Chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($daily_data, 'expense_date')); ?>,
            datasets: [{
                label: 'Daily Expenses',
                data: <?php echo json_encode(array_column($daily_data, 'daily_total')); ?>,
                backgroundColor: '#4f46e5',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value;
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>