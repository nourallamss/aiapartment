
<?php
ob_start();
// admin_reports.php
// Admin panel for managing property reports
ob_start();

// Check if user is admin (you can modify this logic based on your admin system)
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
if (!$is_admin) {
    // You can also check for specific admin email addresses
    $admin_emails = ['admin@homeeasy.com', 'support@homeeasy.com']; // Add your admin emails
    $is_admin = isset($_SESSION['email']) && in_array($_SESSION['email'], $admin_emails);
}

if (!$is_admin) {
        include "navbar.php"; 
        echo"<br/>";
        include "not_admin.php"; 
        ob_end_flush();
        exit();

}
ob_end_flush(); // Optional

// Handle report status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $host = 'localhost';
    $db = 'forfree';
    $user = 'root';
    $pass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        if ($_POST['action'] === 'update_status') {
            $report_id = intval($_POST['report_id']);
            $status = $_POST['status'];
            $admin_notes = $_POST['admin_notes'] ?? '';
            
            $stmt = $pdo->prepare("UPDATE property_reports SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $admin_notes, $report_id]);
            
            echo json_encode(['success' => true, 'message' => 'Report status updated successfully']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        error_log("Database error: " . $e->getMessage());
    }
    exit;
}
?>
<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forfree";

// Initialize variables
$message = "";
$messageType = "";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $newUsername = trim($_POST['username']);
                    $newEmail = trim($_POST['email']);
                    
                    if (!empty($newUsername) && !empty($newEmail)) {
                        // Check if username or email already exists
                        $checkSql = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
                        $checkStmt = $conn->prepare($checkSql);
                        $checkStmt->bindParam(':username', $newUsername);
                        $checkStmt->bindParam(':email', $newEmail);
                        $checkStmt->execute();
                        
                        if ($checkStmt->fetchColumn() > 0) {
                            $message = "Username or email already exists!";
                            $messageType = "error";
                        } else {
                            $insertSql = "INSERT INTO users (username, email) VALUES (:username, :email)";
                            $insertStmt = $conn->prepare($insertSql);
                            $insertStmt->bindParam(':username', $newUsername);
                            $insertStmt->bindParam(':email', $newEmail);
                            $insertStmt->execute();
                            
                            $message = "User added successfully!";
                            $messageType = "success";
                        }
                    } else {
                        $message = "Please fill in all fields!";
                        $messageType = "error";
                    }
                    break;
                    
                case 'edit':
                    $editId = $_POST['id'];
                    $editUsername = trim($_POST['username']);
                    $editEmail = trim($_POST['email']);
                    
                    if (!empty($editUsername) && !empty($editEmail)) {
                        // Check if username or email already exists for other users
                        $checkSql = "SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND id != :id";
                        $checkStmt = $conn->prepare($checkSql);
                        $checkStmt->bindParam(':username', $editUsername);
                        $checkStmt->bindParam(':email', $editEmail);
                        $checkStmt->bindParam(':id', $editId);
                        $checkStmt->execute();
                        
                        if ($checkStmt->fetchColumn() > 0) {
                            $message = "Username or email already exists!";
                            $messageType = "error";
                        } else {
                            $updateSql = "UPDATE users SET username = :username, email = :email WHERE id = :id";
                            $updateStmt = $conn->prepare($updateSql);
                            $updateStmt->bindParam(':username', $editUsername);
                            $updateStmt->bindParam(':email', $editEmail);
                            $updateStmt->bindParam(':id', $editId);
                            $updateStmt->execute();
                            
                            $message = "User updated successfully!";
                            $messageType = "success";
                        }
                    } else {
                        $message = "Please fill in all fields!";
                        $messageType = "error";
                    }
                    break;
                    
                case 'delete':
                    $deleteId = $_POST['id'];
                    $deleteSql = "DELETE FROM users WHERE id = :id";
                    $deleteStmt = $conn->prepare($deleteSql);
                    $deleteStmt->bindParam(':id', $deleteId);
                    $deleteStmt->execute();
                    
                    $message = "User deleted successfully!";
                    $messageType = "success";
                    break;
            }
        }
    }
    
    // Get total users count
    $countSql = "SELECT COUNT(*) as total_users FROM users";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalUsers = $countStmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // Get users created this month (assuming you have a created_at column)
    $monthSql = "SELECT COUNT(*) as new_users FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    try {
        $monthStmt = $conn->prepare($monthSql);
        $monthStmt->execute();
        $newUsers = $monthStmt->fetch(PDO::FETCH_ASSOC)['new_users'];
    } catch (PDOException $e) {
        // If created_at column doesn't exist, use total users
        $newUsers = $totalUsers;
    }
    
    $activeUsers = $totalUsers; // Placeholder - would normally count active users
    
    // Get all users
    $sql = "SELECT * FROM users ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .table-title {
            font-size: 1.5rem;
            margin: 0;
        }

        .search-bar {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input {
            padding: 10px 15px;
            border: none;
            border-radius: 25px;
            outline: none;
            width: 250px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #2196F3;
            color: white;
        }

        .btn-secondary:hover {
            background: #1976D2;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .no-users {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 1.1rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h2 {
            color: #333;
            margin: 0;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .message {
            padding: 15px;
            margin: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-bar {
                justify-content: center;
            }

            .search-input {
                width: 100%;
            }

            .actions {
                flex-direction: column;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <?php include_once "navbar.php"?>
    <br/>
    <main class="main-content">
        <header class="header">
            <h1><i class="fas fa-users"></i> User Management Dashboard</h1>
            <p>Manage and monitor all users in your system</p>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo htmlspecialchars($totalUsers); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo htmlspecialchars($activeUsers); ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo htmlspecialchars($newUsers); ?></div>
                <div class="stat-label">New This Month</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title"><i class="fas fa-database"></i> Users Database</h2>
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="🔍 Search users..." id="searchInput">
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </div>

            <div id="tableContent">
                <?php if (count($users) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-user"></i> Username</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-secondary btn-small" onclick="openEditModal(<?php echo htmlspecialchars($user['id']); ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['email']); ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-small" onclick="confirmDelete(<?php echo htmlspecialchars($user['id']); ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-users">
                        <i class="fas fa-users" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                        <br>No users found in the database.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Add User Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="addUsername"><i class="fas fa-user"></i> Username:</label>
                    <input type="text" id="addUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="addEmail"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="addEmail" name="email" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit User</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="editId" name="id">
                <div class="form-group">
                    <label for="editUsername"><i class="fas fa-user"></i> Username:</label>
                    <input type="text" id="editUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="editEmail"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" id="deleteId" name="id">
    </form>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#tableContent table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
            document.getElementById('addUsername').focus();
        }

        function openEditModal(id, username, email) {
            document.getElementById('editId').value = id;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('editUsername').focus();
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function confirmDelete(id, username) {
            if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // Auto-hide messages after 5 seconds
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>

<?php
$conn = null;
?>