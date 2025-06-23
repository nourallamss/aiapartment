
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Property Reports | HomeEasy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), #6610f2);
            color: white;
            padding: 2rem 0;
        }
        
        .report-card {
            border-left: 4px solid var(--warning-color);
            margin-bottom: 1rem;
        }
        
        .report-card.pending {
            border-left-color: var(--warning-color);
        }
        
        .report-card.reviewed {
            border-left-color: var(--primary-color);
        }
        
        .report-card.resolved {
            border-left-color: var(--success-color);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .property-preview {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include "navbar.php"; ?>
    
    <div class="admin-header">
        <div class="container">
            <h1><i class="fas fa-shield-alt me-2"></i>Admin Panel - Property Reports</h1>
            <p class="mb-0">Manage and review property reports from users</p>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Statistics Row -->
        <div class="row mb-4">
            <?php
            $host = 'localhost';
            $db = 'forfree';
            $user = 'root';
            $pass = '';
            
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Get statistics
                $stats = [];
                $stmt = $pdo->query("SELECT 
                    COUNT(*) as total_reports,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
                    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_reports,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_reports
                    FROM property_reports");
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_reports']; ?></div>
                    <div class="text-muted">Total Reports</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number text-warning"><?php echo $stats['pending_reports']; ?></div>
                    <div class="text-muted">Pending</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number text-primary"><?php echo $stats['reviewed_reports']; ?></div>
                    <div class="text-muted">Reviewed</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number text-success"><?php echo $stats['resolved_reports']; ?></div>
                    <div class="text-muted">Resolved</div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                    All Reports <span class="badge bg-secondary ms-1"><?php echo $stats['total_reports']; ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    Pending <span class="badge bg-warning ms-1"><?php echo $stats['pending_reports']; ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviewed-tab" data-bs-toggle="tab" data-bs-target="#reviewed" type="button" role="tab">
                    Reviewed <span class="badge bg-primary ms-1"><?php echo $stats['reviewed_reports']; ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved" type="button" role="tab">
                    Resolved <span class="badge bg-success ms-1"><?php echo $stats['resolved_reports']; ?></span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="reportTabsContent">
            <?php
            // Get all reports with property details
            $stmt = $pdo->query("
                SELECT pr.*, d.location, d.email as property_owner_email, d.sale, d.rooms, d.bathrooms, d.space, d.images
                FROM property_reports pr
                JOIN data d ON pr.property_id = d.id
                ORDER BY pr.created_at DESC
            ");
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Function to render reports
            function renderReports($reports, $filter = 'all') {
                foreach ($reports as $report) {
                    if ($filter !== 'all' && $report['status'] !== $filter) continue;
                    
                    $status_class = '';
                    $status_text = '';
                    
                    switch($report['status']) {
                        case 'pending':
                            $status_class = 'warning';
                            $status_text = 'Pending Review';
                            break;
                        case 'reviewed':
                            $status_class = 'primary';
                            $status_text = 'Under Review';
                            break;
                        case 'resolved':
                            $status_class = 'success';
                            $status_text = 'Resolved';
                            break;
                    }
                    
                    $created_date = new DateTime($report['created_at']);
                    $formatted_date = $created_date->format('M j, Y \a\t g:i A');
                    $formatted_price = number_format(floatval($report['sale']), 2);
                    
                    echo "<div class='card report-card {$report['status']}'>
                            <div class='card-body'>
                                <div class='d-flex justify-content-between align-items-start mb-3'>
                                    <h6 class='card-title mb-0'>Report #{$report['id']}</h6>
                                    <span class='badge bg-{$status_class} status-badge'>{$status_text}</span>
                                </div>
                                
                                <div class='property-preview'>
                                    <div class='row'>
                                        <div class='col-md-8'>
                                            <h6 class='text-primary'>{$report['location']}</h6>
                                            <p class='mb-1'>
                                                <i class='fas fa-bed me-1'></i> {$report['rooms']} beds
                                                <i class='fas fa-bath ms-2 me-1'></i> {$report['bathrooms']} baths
                                                <i class='fas fa-ruler-combined ms-2 me-1'></i> {$report['space']} m²
                                            </p>
                                            <p class='mb-1'><strong>Price:</strong> \${$formatted_price}</p>
                                            <p class='mb-0'><strong>Owner:</strong> {$report['property_owner_email']}</p>
                                        </div>
                                        <div class='col-md-4 text-end'>
                                            <a href='?page=view&id={$report['property_id']}' class='btn btn-sm btn-outline-primary' target='_blank'>
                                                <i class='fas fa-external-link-alt'></i> View Property
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <p class='mb-1'><strong>Reported by:</strong> {$report['user_email']}</p>
                                        <p class='mb-1'><strong>Date:</strong> {$formatted_date}</p>
                                        <p class='mb-1'><strong>Reason:</strong> {$report['reason']}</p>
                                    </div>
                                    <div class='col-md-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>Status:</strong></label>
                                            <select class='form-select form-select-sm status-select' data-report-id='{$report['id']}'>
                                                <option value='pending'" . ($report['status'] === 'pending' ? ' selected' : '') . ">Pending</option>
                                                <option value='reviewed'" . ($report['status'] === 'reviewed' ? ' selected' : '') . ">Under Review</option>
                                                <option value='resolved'" . ($report['status'] === 'resolved' ? ' selected' : '') . ">Resolved</option>
                                            </select>
                                        </div>
                                        
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>Admin Notes:</strong></label>
<textarea class='form-control form-control-sm admin-notes' data-report-id='{$report['id']}' rows='2' placeholder='Add notes...'>" . ($report['admin_notes'] ?? '') . "</textarea>
                                        </div>
                                        
                                        <button onclick='refreshMultipleTimes(2)' class='btn btn-primary btn-sm update-report' data-report-id='{$report['id']}'>
                                            <i class='fas fa-save'></i> Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>";
                }
            }
            ?>
            
            <!-- All Reports Tab -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <?php renderReports($reports, 'all'); ?>
            </div>
            
            <!-- Pending Reports Tab -->
            <div class="tab-pane fade" id="pending" role="tabpanel">
                <?php renderReports($reports, 'pending'); ?>
            </div>
            
            <!-- Reviewed Reports Tab -->
            <div class="tab-pane fade" id="reviewed" role="tabpanel">
                <?php renderReports($reports, 'reviewed'); ?>
            </div>
            
            <!-- Resolved Reports Tab -->
            <div class="tab-pane fade" id="resolved" role="tabpanel">
                <?php renderReports($reports, 'resolved'); ?>
            </div>
        </div>
        
        <?php
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>
                        <i class='fas fa-exclamation-triangle me-2'></i> 
                        <strong>Error:</strong> Unable to load reports. Please try again later.
                    </div>";
                error_log("Database error: " . $e->getMessage());
            }
        ?>
    </div>

    <!-- Toast notifications -->
    <!-- <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong class="me-auto">Admin Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <!-- Message will be inserted here -->
            <!-- </div>
        </div>
    </div> --> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update report functionality
            document.querySelectorAll('.update-report').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var reportId = this.getAttribute('data-report-id');
                    var statusSelect = document.querySelector('.status-select[data-report-id="' + reportId + '"]');
                    var adminNotes = document.querySelector('.admin-notes[data-report-id="' + reportId + '"]');
                    
                    var status = statusSelect.value;
                    var notes = adminNotes.value;
                    
                    // Add loading state
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                    
                    // Send AJAX request
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=update_status&report_id=' + reportId + '&status=' + status + '&admin_notes=' + encodeURIComponent(notes)
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-save"></i> Update';
                        
                        if (data.success) {
                            showToast(data.message, 'success');
                            
                            // Update the status badge
                            var card = this.closest('.report-card');
                            var badge = card.querySelector('.status-badge');
                            
                            card.className = 'card report-card ' + status;
                            
                            switch(status) {
                                case 'pending':
                                    badge.className = 'badge bg-warning status-badge';
                                    badge.textContent = 'Pending Review';
                                    break;
                                case 'reviewed':
                                    badge.className = 'badge bg-primary status-badge';
                                    badge.textContent = 'Under Review';
                                    break;
                                case 'resolved':
                                    badge.className = 'badge bg-success status-badge';
                                    badge.textContent = 'Resolved';
                                    break;
                            }
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-save"></i> Update';
                        showToast('An error occurred. Please try again.', 'danger');
                        console.error('Error:', error);
                    });
                });
            });
        });
        
        // Toast notification function
        function showToast(message, type = 'info') {
            var toast = document.getElementById('actionToast');
            var toastBody = toast.querySelector('.toast-body');
            var toastIcon = toast.querySelector('.toast-header i');
            
            toastBody.textContent = message;
            
            toastIcon.className = 'fas me-2';
            switch(type) {
                case 'success':
                    toastIcon.classList.add('fa-check-circle', 'text-success');
                    break;
                case 'danger':
                    toastIcon.classList.add('fa-exclamation-circle', 'text-danger');
                    break;
                default:
                    toastIcon.classList.add('fa-info-circle', 'text-primary');
            }
            
            var bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    </script>
       <script>
    // Save current count in sessionStorage
    function refreshMultipleTimes(times) {
      sessionStorage.setItem('refreshCount', times);
      sessionStorage.setItem('scrollPos', window.scrollY);
      location.reload();
    }

    window.addEventListener('load', () => {
      const count = parseInt(sessionStorage.getItem('refreshCount'), 10);
      const scrollPos = sessionStorage.getItem('scrollPos');

      if (scrollPos !== null) {
        window.scrollTo(0, parseInt(scrollPos, 10));
      }

      if (!isNaN(count) && count > 1) {
        sessionStorage.setItem('refreshCount', count - 1);
        setTimeout(() => {
          location.reload();
        }, 1000); // 1 second delay between refreshes
      } else {
        sessionStorage.removeItem('refreshCount');
        sessionStorage.removeItem('scrollPos');
      }
    });
  </script>
</body>
</html>