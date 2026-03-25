<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get expense ID from URL
$expense_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch expense details
$query = "SELECT * FROM expenses WHERE expense_id = :expense_id AND user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':expense_id', $expense_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {
    header('Location: view_expenses.php');
    exit();
}

// Get categories for dropdown (only expense categories)
$query = "SELECT * FROM categories WHERE user_id = :user_id AND category_type = 'expense' ORDER BY category_name";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = $_POST['category_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';
    $expense_date = $_POST['expense_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';
    
    if (empty($category_id) || empty($amount) || empty($expense_date)) {
        $error = 'Please fill in all required fields';
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = 'Please enter a valid amount';
    } else {
        $query = "UPDATE expenses 
                  SET category_id = :category_id, 
                      amount = :amount, 
                      description = :description, 
                      expense_date = :expense_date, 
                      payment_method = :payment_method 
                  WHERE expense_id = :expense_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':expense_date', $expense_date);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':expense_id', $expense_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $message = 'Expense updated successfully!';
            // Refresh expense data
            $expense['category_id'] = $category_id;
            $expense['amount'] = $amount;
            $expense['description'] = $description;
            $expense['expense_date'] = $expense_date;
            $expense['payment_method'] = $payment_method;
        } else {
            $error = 'Error updating expense';
        }
    }
}
?>

<div class="main-content">
    <h1>Edit Expense</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="POST" action="">
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" 
                            <?php echo $expense['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount (₹) *</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                       value="<?php echo $expense['amount']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($expense['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="expense_date">Date *</label>
                <input type="date" class="form-control" id="expense_date" name="expense_date" 
                       value="<?php echo $expense['expense_date']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select class="form-control" id="payment_method" name="payment_method">
                    <option value="cash" <?php echo $expense['payment_method'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="card" <?php echo $expense['payment_method'] == 'card' ? 'selected' : ''; ?>>Card</option>
                    <option value="bank_transfer" <?php echo $expense['payment_method'] == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="other" <?php echo $expense['payment_method'] == 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Expense</button>
            <a href="view_expenses.php" class="btn" style="background: #e5e7eb;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>