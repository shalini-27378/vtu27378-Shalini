<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $category_name = trim($_POST['category_name']);
        $category_type = $_POST['category_type'];
        
        if (!empty($category_name)) {
            try {
                // Check if category already exists for this user
                $check_query = "SELECT category_id FROM categories 
                               WHERE user_id = :user_id AND category_name = :name";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bindParam(':user_id', $user_id);
                $check_stmt->bindParam(':name', $category_name);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    $error = 'Category already exists!';
                } else {
                    $query = "INSERT INTO categories (user_id, category_name, category_type) 
                              VALUES (:user_id, :name, :type)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':name', $category_name);
                    $stmt->bindParam(':type', $category_type);
                    
                    if ($stmt->execute()) {
                        $message = 'Category added successfully!';
                    } else {
                        $error = 'Error adding category';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } else {
            $error = 'Please enter a category name';
        }
    }
    
    // Handle Edit Category
    elseif ($_POST['action'] == 'edit') {
        $category_id = $_POST['category_id'];
        $category_name = trim($_POST['category_name']);
        $category_type = $_POST['category_type'];
        
        if (!empty($category_name)) {
            try {
                // Check if name already exists for another category
                $check_query = "SELECT category_id FROM categories 
                               WHERE user_id = :user_id AND category_name = :name 
                               AND category_id != :category_id";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bindParam(':user_id', $user_id);
                $check_stmt->bindParam(':name', $category_name);
                $check_stmt->bindParam(':category_id', $category_id);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    $error = 'Another category with this name already exists!';
                } else {
                    $query = "UPDATE categories 
                              SET category_name = :name, category_type = :type 
                              WHERE category_id = :category_id AND user_id = :user_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':name', $category_name);
                    $stmt->bindParam(':type', $category_type);
                    $stmt->bindParam(':category_id', $category_id);
                    $stmt->bindParam(':user_id', $user_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Category updated successfully!';
                    } else {
                        $error = 'Error updating category';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } else {
            $error = 'Please enter a category name';
        }
    }
    
    // Handle Delete Category
    elseif ($_POST['action'] == 'delete') {
        $category_id = $_POST['category_id'];
        
        try {
            // Check if category has expenses
            $check_query = "SELECT COUNT(*) as count FROM expenses WHERE category_id = :category_id";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':category_id', $category_id);
            $check_stmt->execute();
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $error = 'Cannot delete category with existing expenses. Please reassign or delete expenses first.';
            } else {
                $query = "DELETE FROM categories WHERE category_id = :category_id AND user_id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':user_id', $user_id);
                
                if ($stmt->execute()) {
                    $message = 'Category deleted successfully!';
                } else {
                    $error = 'Error deleting category';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all categories for this user with expense counts
$query = "SELECT 
            c.*,
            COUNT(e.expense_id) as expense_count,
            COALESCE(SUM(e.amount), 0) as total_spent
          FROM categories c
          LEFT JOIN expenses e ON c.category_id = e.category_id
          WHERE c.user_id = :user_id
          GROUP BY c.category_id
          ORDER BY c.category_type, c.category_name";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate income and expense categories
$expense_categories = array_filter($categories, function($cat) {
    return $cat['category_type'] == 'expense';
});

$income_categories = array_filter($categories, function($cat) {
    return $cat['category_type'] == 'income';
});

// Get category statistics
$stats_query = "SELECT 
                  COUNT(*) as total_categories,
                  SUM(CASE WHEN category_type = 'expense' THEN 1 ELSE 0 END) as expense_categories,
                  SUM(CASE WHEN category_type = 'income' THEN 1 ELSE 0 END) as income_categories
                FROM categories 
                WHERE user_id = :user_id";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bindParam(':user_id', $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <h1>Manage Categories</h1>
    
    <!-- Category Statistics -->
    <div class="dashboard-stats" style="margin-bottom: 2rem;">
        <div class="stat-card" style="background: linear-gradient(135deg, #4f46e5, #818cf8);">
            <h3>Total Categories</h3>
            <div class="stat-number"><?php echo $stats['total_categories']; ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #34d399);">
            <h3>Expense Categories</h3>
            <div class="stat-number"><?php echo $stats['expense_categories']; ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
            <h3>Income Categories</h3>
            <div class="stat-number"><?php echo $stats['income_categories']; ?></div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #f87171);">
            <h3>Active Categories</h3>
            <div class="stat-number">
                <?php echo count(array_filter($categories, function($cat) { return $cat['expense_count'] > 0; })); ?>
            </div>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Add Category Form -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; border-radius: 15px; margin-bottom: 2rem; color: white;">
        <h3 style="color: white; margin-bottom: 1rem;">Add New Category</h3>
        <form method="POST" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="action" value="add">
            
            <div style="flex: 2;">
                <label style="color: white; display: block; margin-bottom: 0.5rem;">Category Name</label>
                <input type="text" name="category_name" class="form-control" placeholder="e.g., Groceries, Salary, etc." required>
            </div>
            
            <div style="flex: 1;">
                <label style="color: white; display: block; margin-bottom: 0.5rem;">Category Type</label>
                <select name="category_type" class="form-control" required>
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                </select>
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>
    
    <!-- Expense Categories Section -->
    <h2 style="margin: 2rem 0 1rem;">Expense Categories</h2>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Type</th>
                    <th>Expenses Count</th>
                    <th>Total Spent</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expense_categories)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">
                        No expense categories found. Add one now!
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($expense_categories as $category): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                        <td>
                            <span style="background: #ef4444; color: white; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.85rem;">
                                Expense
                            </span>
                        </td>
                        <td><?php echo $category['expense_count']; ?></td>
                        <td>₹<?php echo number_format($category['total_spent'], 2); ?></td>
                        <td><?php echo date('d M Y', strtotime($category['created_at'])); ?></td>
                        <td>
                            <button onclick="editCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name']); ?>', '<?php echo $category['category_type']; ?>')" 
                                    class="btn btn-primary" style="padding: 0.25rem 0.5rem;">Edit</button>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($category['category_name']); ?>', <?php echo $category['expense_count']; ?>)">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem;" <?php echo $category['expense_count'] > 0 ? 'title="Has associated expenses"' : ''; ?>>Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Income Categories Section -->
    <h2 style="margin: 2rem 0 1rem;">Income Categories</h2>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Type</th>
                    <th>Income Count</th>
                    <th>Total Received</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($income_categories)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">
                        No income categories found. Add one now!
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($income_categories as $category): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                        <td>
                            <span style="background: #10b981; color: white; padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.85rem;">
                                Income
                            </span>
                        </td>
                        <td><?php echo $category['expense_count']; ?></td>
                        <td>₹<?php echo number_format($category['total_spent'], 2); ?></td>
                        <td><?php echo date('d M Y', strtotime($category['created_at'])); ?></td>
                        <td>
                            <button onclick="editCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name']); ?>', '<?php echo $category['category_type']; ?>')" 
                                    class="btn btn-primary" style="padding: 0.25rem 0.5rem;">Edit</button>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($category['category_name']); ?>', <?php echo $category['expense_count']; ?>)">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem;" <?php echo $category['expense_count'] > 0 ? 'title="Has associated records"' : ''; ?>>Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Category Usage Tips -->
    <div style="margin-top: 2rem; padding: 1rem; background: #f3f4f6; border-radius: 10px;">
        <h3 style="margin-bottom: 1rem;">💡 Category Management Tips</h3>
        <ul style="list-style-type: none; padding: 0;">
            <li style="margin-bottom: 0.5rem;">✓ Use clear, descriptive names for your categories</li>
            <li style="margin-bottom: 0.5rem;">✓ Create separate categories for different expense types (Food, Transport, Entertainment, etc.)</li>
            <li style="margin-bottom: 0.5rem;">✓ Income categories help track different sources of money (Salary, Freelance, Investments, etc.)</li>
            <li style="margin-bottom: 0.5rem;">✓ Categories with existing expenses cannot be deleted (to maintain data integrity)</li>
            <li style="margin-bottom: 0.5rem;">✓ You can edit category names and types anytime</li>
        </ul>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; width: 90%; max-width: 500px; margin: 100px auto; padding: 2rem; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="margin-bottom: 1.5rem;">Edit Category</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="category_id" id="edit_category_id">
            
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="category_name" id="edit_category_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Category Type</label>
                <select name="category_type" id="edit_category_type" class="form-control" required>
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Update Category</button>
                <button type="button" class="btn" onclick="closeEditModal()" style="background: #e5e7eb;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name, type) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_category_type').value = type;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function confirmDelete(categoryName, expenseCount) {
    if (expenseCount > 0) {
        alert(`Cannot delete "${categoryName}" because it has ${expenseCount} associated expenses.`);
        return false;
    }
    return confirm(`Are you sure you want to delete category "${categoryName}"?`);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>