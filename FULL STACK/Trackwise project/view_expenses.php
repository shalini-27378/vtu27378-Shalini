<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Handle delete request
if (isset($_GET['delete'])) {
    $expense_id = $_GET['delete'];
    $query = "DELETE FROM expenses WHERE expense_id = :expense_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':expense_id', $expense_id);
    $stmt->bindParam(':user_id', $user_id);
    if ($stmt->execute()) {
        header('Location: view_expenses.php?msg=deleted');
    } else {
        header('Location: view_expenses.php?msg=error');
    }
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total records
$query = "SELECT COUNT(*) as total FROM expenses WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);

// Get expenses with category names
$query = "SELECT e.*, c.category_name 
          FROM expenses e 
          LEFT JOIN categories c ON e.category_id = c.category_id 
          WHERE e.user_id = :user_id 
          ORDER BY e.expense_date DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Show success/error message
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        echo '<div class="alert alert-success">Expense deleted successfully!</div>';
    } elseif ($_GET['msg'] == 'error') {
        echo '<div class="alert alert-danger">Error deleting expense!</div>';
    }
}
?>

<div class="main-content">
    <h1>View Expenses</h1>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">
                        No expenses found. <a href="add_expense.php">Add your first expense!</a>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($expense['expense_date'])); ?></td>
                        <td><?php echo htmlspecialchars($expense['category_name'] ?? 'Uncategorized'); ?></td>
                        <td><?php echo htmlspecialchars($expense['description'] ?? '-'); ?></td>
                        <td><strong>₹<?php echo number_format($expense['amount'], 2); ?></strong></td>
                        <td><?php echo ucfirst($expense['payment_method']); ?></td>
                        <td>
                            <a href="edit_expense.php?id=<?php echo $expense['expense_id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem; text-decoration: none;">Edit</a>
                            <a href="?delete=<?php echo $expense['expense_id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; text-decoration: none;" onclick="return confirm('Are you sure you want to delete this expense?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="margin-top: 2rem; text-align: center;">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="btn" style="margin: 0 0.25rem; <?php echo $page == $i ? 'background: var(--primary-color); color: white;' : 'background: #e5e7eb;'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>