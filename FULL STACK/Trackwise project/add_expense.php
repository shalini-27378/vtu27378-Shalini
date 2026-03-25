<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get categories for dropdown
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
        $query = "INSERT INTO expenses (user_id, category_id, amount, description, expense_date, payment_method) 
                  VALUES (:user_id, :category_id, :amount, :description, :expense_date, :payment_method)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':expense_date', $expense_date);
        $stmt->bindParam(':payment_method', $payment_method);
        
        if ($stmt->execute()) {
            $message = 'Expense added successfully!';
            // Clear form
            $_POST = array();
        } else {
            $error = 'Error adding expense';
        }
    }
}
?>

<div class="main-content">
    <h1>Add New Expense</h1>
    
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
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($categories)): ?>
                    <small style="color: #ef4444;">No categories found. <a href="categories.php">Create a category first!</a></small>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount (₹) *</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="expense_date">Date *</label>
                <input type="date" class="form-control" id="expense_date" name="expense_date" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select class="form-control" id="payment_method" name="payment_method">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Expense</button>
            <a href="index.php" class="btn" style="background: #e5e7eb;">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>