<?php

// Database configuration
class Database {
    private $host = "localhost";
    private $db_name = "feedback_system";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Feedback class
class Feedback {
    private $conn;
    private $table_name = "feedback";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, phone=:phone, category=:category, 
                      rating=:rating, subject=:subject, message=:message, 
                      attachment=:attachment, ip_address=:ip_address, 
                      created_at=:created_at";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":phone", $data['phone']);
        $stmt->bindParam(":category", $data['category']);
        $stmt->bindParam(":rating", $data['rating']);
        $stmt->bindParam(":subject", $data['subject']);
        $stmt->bindParam(":message", $data['message']);
        $stmt->bindParam(":attachment", $data['attachment']);
        $stmt->bindParam(":ip_address", $data['ip_address']);
        $stmt->bindParam(":created_at", $data['created_at']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_feedback,
                    AVG(rating) as avg_rating,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

// Initialize database and feedback object
$database = new Database();
$db = $database->getConnection();
$feedback = new Feedback($db);

// Handle form submission
$success_message = "";
$error_message = "";

if ($_POST && isset($_POST['submit_feedback'])) {
    $errors = [];
    
    // Validate required fields
    if (empty($_POST['name'])) $errors[] = "Name is required";
    if (empty($_POST['email'])) $errors[] = "Email is required";
    if (empty($_POST['category'])) $errors[] = "Category is required";
    if (empty($_POST['subject'])) $errors[] = "Subject is required";
    if (empty($_POST['message'])) $errors[] = "Message is required";
    if (empty($_POST['rating'])) $errors[] = "Rating is required";
    
    // Validate email format
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Handle file upload
    $attachment_path = "";
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Invalid file type. Allowed: " . implode(', ', $allowed_types);
        } elseif ($_FILES['attachment']['size'] > 5000000) { // 5MB limit
            $errors[] = "File size too large. Maximum 5MB allowed";
        } else {
            $upload_dir = "uploads/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $attachment_path = $upload_dir . time() . "_" . $_FILES['attachment']['name'];
            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path)) {
                $errors[] = "Failed to upload file";
            }
        }
    }
    
    if (empty($errors)) {
        $feedback_data = [
            'name' => htmlspecialchars($_POST['name']),
            'email' => htmlspecialchars($_POST['email']),
            'phone' => htmlspecialchars($_POST['phone']),
            'category' => htmlspecialchars($_POST['category']),
            'rating' => (int)$_POST['rating'],
            'subject' => htmlspecialchars($_POST['subject']),
            'message' => htmlspecialchars($_POST['message']),
            'attachment' => $attachment_path,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($feedback->create($feedback_data)) {
            $success_message = "Thank you! Your feedback has been submitted successfully.";
            // Send email notification (optional)
            // mail($_POST['email'], "Feedback Received", "Thank you for your feedback!");
        } else {
            $error_message = "Sorry, there was an error submitting your feedback.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Handle admin actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] == 'resolve') {
        $feedback->updateStatus($_GET['id'], 'resolved');
    } elseif ($_GET['action'] == 'pending') {
        $feedback->updateStatus($_GET['id'], 'pending');
    }
}

// Get feedback list for admin view
$feedback_list = $feedback->read();
$stats = $feedback->getStats();

// Check if admin view is requested
$show_admin = isset($_GET['admin']) && $_GET['admin'] == 'true';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Feedback System</title>
    

<style>
    /* Reset and base styles */
    
    /* Transparent Navbar */
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        padding: 20px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        z-index: 1000;
        transition: all 0.3s ease;
    }
    
    .navbar:hover {
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .logo {
        font-size: 24px;
        font-weight: bold;
        color: white;
        text-decoration: none;
    }
    
    .nav-links {
        display: flex;
        gap: 30px;
    }
    
    .nav-links a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        position: relative;
        padding: 5px 0;
        transition: all 0.3s ease;
    }
    
    .nav-links a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(to right, #ff512f, #dd2476);
        transition: width 0.3s ease;
    }
    
    .nav-links a:hover::after {
        width: 100%;
    }
    
    /* Rocket Launch Section */
    .launch-container {
        position: fixed;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .launch-btn {
        position: absolute;
        padding: 15px 30px;
        font-size: 18px;
        background: linear-gradient(to right, #ff512f, #dd2476);
        color: white;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(221, 36, 118, 0.4);
        transition: all 0.3s ease;
        z-index: 10;
    }
    
    .launch-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(221, 36, 118, 0.6);
    }
    
    .rocket {
        position: absolute;
        width: 80px;
        transform: translate(-50%, 0);
        transition: transform 0.1s linear;
        z-index: 5;
        display: none;
        bottom: 50%;
        left: 50%;
    }
    
    .launching {
        display: block;
        animation: launch 3s cubic-bezier(0.1, 0.8, 0.2, 1) forwards;
    }
    
    @keyframes launch {
        0% {
            transform: translate(-50%, 0) rotate(0deg) scale(1);
        }
        20% {
            transform: translate(-50%, -100px) rotate(5deg);
        }
        40% {
            transform: translate(-50%, -250px) rotate(-2deg);
        }
        60% {
            transform: translate(-50%, -500px) rotate(0deg);
        }
        80% {
            transform: translate(-50%, -800px) scale(0.7);
        }
        100% {
            transform: translate(-50%, -120vh) scale(0.3);
        }
    }
    
    /* Stars Background */
    .stars {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .star {
        position: absolute;
        background-color: white;
        border-radius: 50%;
        animation: twinkle 2s infinite alternate;
    }
    
    @keyframes twinkle {
        0% {
            opacity: 0.2;
        }
        100% {
            opacity: 1;
        }
    }
    
    /* Content to demonstrate scrolling */
    .content {
        padding: 100px 40px;
        margin-top: 80px;
        height: 200vh;
    }
</style>
<style>
    body {
        background: linear-gradient(to bottom, #1a1a2e, #16213e);
        font-family: Arial, sans-serif;
    }
    
    .launch-container {
        position: relative;
        width: 100%;
        height: 10vh;
        display: flex;
        justify-content: center;
    }
    
    .launch-btn {
        position: absolute;
        bottom: 20%;
        padding: 15px 30px;
        font-size: 18px;
        background: linear-gradient(to right, #ff512f, #dd2476);
        color: white;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(221, 36, 118, 0.4);
        transition: all 0.3s ease;
        z-index: 10;
    }
    
    .launch-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(221, 36, 118, 0.6);
    }
    
    .rocket {
        position: absolute;
        width: 80px;
        bottom: 20%;
        transform: translateY(0);
        transition: transform 0.1s linear;
        z-index: 5;
        display: none;
    }
    
    .launching {
        display: block;
        animation: launch 4s cubic-bezier(0.1, 0.8, 0.2, 1) forwards;
    }
    
    .smoke {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #ff9d00;
        filter: blur(5px);
        opacity: 0;
        z-index: 4;
    }
    
    .fire {
        position: absolute;
        width: 20px;
        height: 30px;
        background: linear-gradient(to top, #ff9d00, #ff2d00);
        border-radius: 50% 50% 20% 20%;
        filter: blur(5px);
        opacity: 0;
        z-index: 3;
        transform-origin: center top;
    }
    
    @keyframes launch {
        0% {
            transform: translateY(0) rotate(0deg);
        }
        10% {
            transform: translateY(-50px) rotate(5deg);
        }
        20% {
            transform: translateY(-120px) rotate(-3deg);
        }
        30% {
            transform: translateY(-220px) rotate(2deg);
        }
        50% {
            transform: translateY(-500px) rotate(0deg);
        }
        70% {
            transform: translateY(-800px) scale(0.8);
        }
        100% {
            transform: translateY(-120vh) scale(0.3);
        }
    }
    
    @keyframes smoke {
        0% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translateY(-100px) scale(3);
            opacity: 0;
        }
    }
    
    @keyframes fire {
        0% {
            transform: scaleY(1);
            opacity: 1;
        }
        50% {
            transform: scaleY(1.5);
        }
        100% {
            transform: scaleY(0.5);
            opacity: 0.5;
        }
    }
    
    @keyframes twinkle {
        0% {
            opacity: 0.2;
        }
        100% {
            opacity: 1;
        }
    }
</style>
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .header {
        background: linear-gradient(135deg, #2c3e50, #34495e);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .tab-container {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .tab {
        flex: 1;
        padding: 15px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }

    .tab.active {
        background: white;
        border-bottom: 3px solid #667eea;
        color: #667eea;
    }

    .tab-content {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    input, select, textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    textarea {
        resize: vertical;
        min-height: 120px;
    }

    .rating-container {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .rating-star {
        cursor: pointer;
        font-size: 24px;
        color: #ccc;
        transition: color 0.2s;
    }

    .rating-star.active {
        color: #ffc107;
    }

    .btn {
        display: inline-block;
        padding: 12px 24px;
        background: linear-gradient(to right, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
    }

    .stat-number {
        font-size: 2.5em;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .feedback-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .feedback-table th,
    .feedback-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .feedback-table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-resolved {
        background: #d4edda;
        color: #155724;
    }

    .action-btn {
        padding: 5px 10px;
        margin: 2px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-resolve {
        background: #28a745;
        color: white;
    }

    .btn-pending {
        background: #ffc107;
        color: black;
    }

    .hidden {
        display: none;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .tab-container {
            flex-direction: column;
        }
    }
</style>
</head>
<body>
    <div  id="stars"></div>

      <nav class="navbar">
        <a href="#" class="logo">FeedBack</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="?page=buy">Properties</a>
            <a href="?page=rent">Rent</a>
            <a href="?page=sell">Sell</a>
        </div>
    </nav>
         <br/>
          <br/>
    <div class="container">
        <div class="header">
            <h1>🚀 Advanced Feedback System</h1>
            <p>We value your opinion and feedback</p>
        </div>

        <div class="tab-container">
            <button class="tab <?php echo !$show_admin ? 'active' : ''; ?>" onclick="showTab('feedback')">
                📝 Submit Feedback
            </button>
            <button class="tab <?php echo $show_admin ? 'active' : ''; ?>" onclick="showTab('admin')">
                👨‍💼 Admin Dashboard
            </button>
        </div>

        <!-- Feedback Form Tab -->
        <div id="feedback-tab" class="tab-content <?php echo $show_admin ? 'hidden' : ''; ?>">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="bug_report" <?php echo (isset($_POST['category']) && $_POST['category'] == 'bug_report') ? 'selected' : ''; ?>>🐛 Bug Report</option>
                            <option value="feature_request" <?php echo (isset($_POST['category']) && $_POST['category'] == 'feature_request') ? 'selected' : ''; ?>>✨ Feature Request</option>
                            <option value="general_feedback" <?php echo (isset($_POST['category']) && $_POST['category'] == 'general_feedback') ? 'selected' : ''; ?>>💬 General Feedback</option>
                            <option value="support" <?php echo (isset($_POST['category']) && $_POST['category'] == 'support') ? 'selected' : ''; ?>>🆘 Support</option>
                            <option value="complaint" <?php echo (isset($_POST['category']) && $_POST['category'] == 'complaint') ? 'selected' : ''; ?>>😞 Complaint</option>
                            <option value="praise" <?php echo (isset($_POST['category']) && $_POST['category'] == 'praise') ? 'selected' : ''; ?>>👏 Praise</option>
                        </select>
                    </div>
                </div>

                <!-- In the HTML part, replace the rating container with this: -->
<div class="form-group">
    <label>Overall Rating *</label>
    <div class="rating-container">
        <span class="rating-star" data-rating="1">⭐</span>
        <span class="rating-star" data-rating="2">⭐</span>
        <span class="rating-star" data-rating="3">⭐</span>
        <span class="rating-star" data-rating="4">⭐</span>
        <span class="rating-star" data-rating="5">⭐</span>
        <span id="rating-text" style="margin-left: 10px; font-weight: bold;">Click to rate</span>
    </div>
    <input type="hidden" id="rating" name="rating" required>
</div>



                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required 
                           value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="message">Your Message *</label>
                    <textarea id="message" name="message" required 
                              placeholder="Please provide detailed feedback..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="attachment">Attachment (Optional)</label>
                    <input type="file" id="attachment" name="attachment" 
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                    <small>Allowed formats: JPG, PNG, GIF, PDF, DOC, DOCX (Max 5MB)</small>
                </div><!-- Replace the launch button section with this: -->

               <center>
    <div class="launch-container">
        <button type="submit" name="submit_feedback" class="launch-btn" id="launchBtn">🚀 Launch Feedback</button>
        <img src="https://www.freeiconspng.com/uploads/rocket-ship-png-14.png" class="rocket" id="rocket" alt="Rocket">
    </div>
</center>

            </form>
        </div>

<!-- Admin Dashboard Tab -->
        <div id="admin-tab" class="tab-content <?php echo !$show_admin ? 'hidden' : ''; ?>">
            <h2>📊 Dashboard Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_feedback']; ?></div>
                    <div>Total Feedback</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?>⭐</div>
                    <div>Average Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_count']; ?></div>
                    <div>Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['resolved_count']; ?></div>
                    <div>Resolved</div>
                </div>
            </div>

            <h3>📋 Recent Feedback</h3>
            <table class="feedback-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Rating</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $feedback_list->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo ucwords(str_replace('_', ' ', $row['category'])); ?></td>
                        <td><?php echo str_repeat('⭐', $row['rating']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['subject'], 0, 30)); ?>...</td>
                        <td>
                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <a href="?admin=true&action=resolve&id=<?php echo $row['id']; ?>" 
                                   class="action-btn btn-resolve">Resolve</a>
                            <?php else: ?>
                                <a href="?admin=true&action=pending&id=<?php echo $row['id']; ?>" 
                                   class="action-btn btn-pending">Reopen</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<br/>
<br/>
<br/>
<br/>

     <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tabs
            document.getElementById('feedback-tab').classList.add('hidden');
            document.getElementById('admin-tab').classList.add('hidden');
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab and activate button
            if (tabName === 'feedback') {
                document.getElementById('feedback-tab').classList.remove('hidden');
                document.querySelector('.tab:first-child').classList.add('active');
                history.pushState(null, null, window.location.pathname);
            } else {
                document.getElementById('admin-tab').classList.remove('hidden');
                document.querySelector('.tab:last-child').classList.add('active');
                history.pushState(null, null, '?admin=true');
            }
        }

        // Star rating functionality
        const stars = document.querySelectorAll('.rating-star');
        const ratingInput = document.getElementById('rating');
        const ratingText = document.getElementById('rating-text');

        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                const rating = index + 1;
                ratingInput.value = rating;
                
                // Update stars display
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                
                // Update rating text
                const ratingTexts = ['Terrible', 'Poor', 'Average', 'Good', 'Excellent'];
                ratingText.textContent = ratingTexts[rating - 1];
            });
            
            star.addEventListener('mouseover', () => {
                const rating = index + 1;
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });

        // Reset stars on mouse leave
        document.querySelector('.rating-container').addEventListener('mouseleave', () => {
            const currentRating = ratingInput.value;
            stars.forEach((s, i) => {
                if (i < currentRating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const rating = ratingInput.value;
            if (!rating) {
                e.preventDefault();
                alert('Please provide a rating before submitting.');
                return false;
            }
        });
    </script>
    <script>
        // Create stars
        const starsContainer = document.getElementById('stars');
        for (let i = 0; i < 100; i++) {
            const star = document.createElement('div');
            star.classList.add('star');
            star.style.width = `${Math.random() * 3}px`;
            star.style.height = star.style.width;
            star.style.left = `${Math.random() * 100}%`;
            star.style.top = `${Math.random() * 100}%`;
            star.style.animationDelay = `${Math.random() * 2}s`;
            starsContainer.appendChild(star);
        }
        
        const launchBtn = document.getElementById('launchBtn');
        const rocket = document.getElementById('rocket');
        
        launchBtn.addEventListener('click', function() {
            // Hide button
            this.style.display = 'none';
            
            // Position rocket where button was
            const btnRect = this.getBoundingClientRect();
            rocket.style.left = `${btnRect.left + btnRect.width/2 - 40}px`;
            rocket.style.bottom = `${window.innerHeight - btnRect.bottom + 20}px`;
            
            // Show and launch rocket
            rocket.classList.add('launching');
            
            // Create smoke and fire effects
            createSmokeAndFire();
        });
        
        function createSmokeAndFire() {
            const rocketRect = rocket.getBoundingClientRect();
            const centerX = rocketRect.left + rocketRect.width/2;
            
            // Create initial fire
            const fire = document.createElement('div');
            fire.classList.add('fire');
            fire.style.left = `${centerX - 10}px`;
            fire.style.top = `${rocketRect.bottom}px`;
            document.body.appendChild(fire);
            
            // Animate fire
            fire.animate([
                { opacity: 1, transform: 'scaleY(1)' },
                { opacity: 0.8, transform: 'scaleY(1.2)' },
                { opacity: 0.5, transform: 'scaleY(0.8)' },
                { opacity: 0, transform: 'scaleY(0.2)' }
            ], {
                duration: 300,
                iterations: Infinity
            });
            
            // Create smoke particles
            const smokeInterval = setInterval(() => {
                for (let i = 0; i < 3; i++) {
                    const smoke = document.createElement('div');
                    smoke.classList.add('smoke');
                    smoke.style.left = `${centerX - 5 + Math.random() * 10}px`;
                    smoke.style.top = `${rocketRect.bottom - 10}px`;
                    smoke.style.backgroundColor = `hsl(${Math.random() * 20 + 20}, 100%, 50%)`;
                    smoke.style.width = `${5 + Math.random() * 10}px`;
                    smoke.style.height = smoke.style.width;
                    document.body.appendChild(smoke);
                    
                    // Animate smoke
                    smoke.animate([
                        { transform: 'translateY(0) scale(1)', opacity: 1 },
                        { transform: `translateY(${-50 - Math.random() * 100}px) scale(${2 + Math.random() * 2})`, opacity: 0 }
                    ], {
                        duration: 1000 + Math.random() * 1000,
                        easing: 'cubic-bezier(0.3, 0.6, 0.7, 0.2)'
                    });
                    
                    // Remove smoke after animation
                    setTimeout(() => {
                        smoke.remove();
                    }, 2000);
                }
            }, 100);
            
            // Clean up after launch
            setTimeout(() => {
                clearInterval(smokeInterval);
                fire.remove();
                setTimeout(() => {
                    launchBtn.style.display = 'block';
                    rocket.classList.remove('launching');
                }, 2000);
            }, 4000);
        }
    </script>
    <script>
    // Tab switching functionality
    function showTab(tabName) {
        // Hide all tabs
        document.getElementById('feedback-tab').classList.add('hidden');
        document.getElementById('admin-tab').classList.add('hidden');
        
        // Remove active class from all tab buttons
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Show selected tab and activate button
        if (tabName === 'feedback') {
            document.getElementById('feedback-tab').classList.remove('hidden');
            document.querySelector('.tab:first-child').classList.add('active');
            history.pushState(null, null, window.location.pathname);
        } else {
            document.getElementById('admin-tab').classList.remove('hidden');
            document.querySelector('.tab:last-child').classList.add('active');
            history.pushState(null, null, '?admin=true');
        }
    }

    // Star rating functionality - fixed class name
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating');
    const ratingText = document.getElementById('rating-text');

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;
            ratingInput.value = rating;
            
            // Update stars display
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
            
            // Update rating text
            const ratingTexts = ['Terrible', 'Poor', 'Average', 'Good', 'Excellent'];
            ratingText.textContent = ratingTexts[rating - 1];
        });
        
        star.addEventListener('mouseover', () => {
            const rating = index + 1;
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });

    // Reset stars on mouse leave
    document.querySelector('.rating-container').addEventListener('mouseleave', () => {
        const currentRating = ratingInput.value;
        stars.forEach((s, i) => {
            if (i < currentRating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });

    // Rocket launch functionality
    const launchBtn = document.getElementById('launchBtn');
    const rocket = document.getElementById('rocket');
    
    // Only add click handler if it's the submit button
    if (launchBtn.type === 'submit') {
        launchBtn.addEventListener('click', function(e) {
            const rating = ratingInput.value;
            if (!rating) {
                e.preventDefault();
                alert('Please provide a rating before submitting.');
                return false;
            }
            
            // Hide button
            this.style.display = 'none';
            
            // Position rocket where button was
            const btnRect = this.getBoundingClientRect();
            rocket.style.left = `${btnRect.left + btnRect.width/2 - 40}px`;
            rocket.style.bottom = `${window.innerHeight - btnRect.bottom + 20}px`;
            
            // Show and launch rocket
            rocket.classList.add('launching');
            
            // Create smoke and fire effects
            createSmokeAndFire();
            
            // Submit the form after animation starts
            setTimeout(() => {
                this.closest('form').submit();
            }, 1000);
        });
    }
    
    function createSmokeAndFire() {
        const rocketRect = rocket.getBoundingClientRect();
        const centerX = rocketRect.left + rocketRect.width/2;
        
        // Create initial fire
        const fire = document.createElement('div');
        fire.classList.add('fire');
        fire.style.left = `${centerX - 10}px`;
        fire.style.top = `${rocketRect.bottom}px`;
        document.body.appendChild(fire);
        
        // Animate fire
        fire.animate([
            { opacity: 1, transform: 'scaleY(1)' },
            { opacity: 0.8, transform: 'scaleY(1.2)' },
            { opacity: 0.5, transform: 'scaleY(0.8)' },
            { opacity: 0, transform: 'scaleY(0.2)' }
        ], {
            duration: 300,
            iterations: Infinity
        });
        
        // Create smoke particles
        const smokeInterval = setInterval(() => {
            for (let i = 0; i < 3; i++) {
                const smoke = document.createElement('div');
                smoke.classList.add('smoke');
                smoke.style.left = `${centerX - 5 + Math.random() * 10}px`;
                smoke.style.top = `${rocketRect.bottom - 10}px`;
                smoke.style.backgroundColor = `hsl(${Math.random() * 20 + 20}, 100%, 50%)`;
                smoke.style.width = `${5 + Math.random() * 10}px`;
                smoke.style.height = smoke.style.width;
                document.body.appendChild(smoke);
                
                // Animate smoke
                smoke.animate([
                    { transform: 'translateY(0) scale(1)', opacity: 1 },
                    { transform: `translateY(${-50 - Math.random() * 100}px) scale(${2 + Math.random() * 2})`, opacity: 0 }
                ], {
                    duration: 1000 + Math.random() * 1000,
                    easing: 'cubic-bezier(0.3, 0.6, 0.7, 0.2)'
                });
                
                // Remove smoke after animation
                setTimeout(() => {
                    smoke.remove();
                }, 2000);
            }
        }, 100);
        
        // Clean up after launch
        setTimeout(() => {
            clearInterval(smokeInterval);
            fire.remove();
            setTimeout(() => {
                if (launchBtn) {
                    launchBtn.style.display = 'block';
                }
                if (rocket) {
                    rocket.classList.remove('launching');
                }
            }, 2000);
        }, 4000);
    }

    // Create stars background
    const starsContainer = document.getElementById('stars');
    for (let i = 0; i < 100; i++) {
        const star = document.createElement('div');
        star.classList.add('star');
        star.style.width = `${Math.random() * 3}px`;
        star.style.height = star.style.width;
        star.style.left = `${Math.random() * 100}%`;
        star.style.top = `${Math.random() * 100}%`;
        star.style.animationDelay = `${Math.random() * 2}s`;
        starsContainer.appendChild(star);
    }
</script>
</body>
</html>

<?php
/*
SQL TABLE STRUCTURE - Run this to create the required database table:

CREATE DATABASE feedback_system;

USE feedback_system;

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    category VARCHAR(50) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    attachment VARCHAR(255),
    ip_address VARCHAR(45),
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Optional: Add an admin user table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
*/
?>