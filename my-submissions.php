<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $action = $_POST['action'];
        $id = intval($_POST['id']);
        
        if ($action === 'delete_report') {
            // Delete rescue report
            $stmt = $conn->prepare("DELETE FROM Rescue_Report WHERE report_id = ? AND reporter_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Report deleted successfully!";
            } else {
                $error_message = "Error deleting report.";
            }
            $stmt->close();
        } 
        elseif ($action === 'delete_application') {
            // Delete volunteer application
            $stmt = $conn->prepare("DELETE FROM Volunteer_Application WHERE application_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Application deleted successfully!";
            } else {
                $error_message = "Error deleting application.";
            }
            $stmt->close();
        }
    }
}

// Fetch user's rescue reports
$reports_query = "SELECT * FROM Rescue_Report WHERE reporter_id = ? ORDER BY reported_at DESC";
$stmt = $conn->prepare($reports_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reports_result = $stmt->get_result();
$stmt->close();

// Fetch user's volunteer applications
$applications_query = "SELECT * FROM Volunteer_Application WHERE user_id = ? ORDER BY applied_at DESC";
$stmt = $conn->prepare($applications_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications_result = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<style>
    .dashboard-page {
        background: #f5f7fa;
        padding: 40px 0;
        min-height: calc(100vh - 140px);
    }
    
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .page-header {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        margin: 0 0 10px 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .page-header p {
        margin: 0;
        color: #666;
    }
    
    .tabs {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .tab-btn {
        padding: 15px 30px;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        color: #666;
        transition: all 0.3s;
    }
    
    .tab-btn.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }
    
    .tab-btn:hover {
        color: #667eea;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .items-grid {
        display: grid;
        gap: 20px;
    }
    
    .item-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 20px;
        align-items: start;
    }
    
    .item-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .item-image {
        width: 120px;
        height: 120px;
        border-radius: 10px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: white;
    }
    
    .item-details {
        flex: 1;
    }
    
    .item-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin: 0 0 10px 0;
    }
    
    .item-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 12px;
        font-size: 14px;
        color: #666;
    }
    
    .item-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .status-badge {
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-approved {
        background: #d4edda;
        color: #155724;
    }
    
    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-submitted {
        background: #cfe2ff;
        color: #084298;
    }
    
    .status-under-review {
        background: #cff4fc;
        color: #055160;
    }
    
    .status-resolved {
        background: #d1e7dd;
        color: #0f5132;
    }
    
    .item-description {
        color: #666;
        font-size: 14px;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .item-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .btn-action {
        padding: 8px 20px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }
    
    .btn-edit {
        background: #667eea;
        color: white;
    }
    
    .btn-edit:hover {
        background: #5568d3;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    .btn-view {
        background: #17a2b8;
        color: white;
    }
    
    .btn-view:hover {
        background: #138496;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }
    
    .empty-state h3 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: #666;
        margin-bottom: 20px;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    @media (max-width: 768px) {
        .item-card {
            grid-template-columns: 1fr;
        }
        
        .item-actions {
            flex-direction: row;
        }
        
        .tabs {
            overflow-x: auto;
        }
    }
</style>

<section class="dashboard-page">
    <div class="dashboard-container">
        <div class="page-header">
            <h1>📋 My Submissions</h1>
            <p>View and manage your rescue reports and volunteer applications</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <span style="font-size: 20px;">✓</span>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <span style="font-size: 20px;">✗</span>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('reports')">
                📋 My Reports (<?php echo $reports_result->num_rows; ?>)
            </button>
            <button class="tab-btn" onclick="switchTab('applications')">
                🤝 My Applications (<?php echo $applications_result->num_rows; ?>)
            </button>
        </div>
        
        <!-- Reports Tab -->
        <div id="reports-tab" class="tab-content active">
            <?php if ($reports_result->num_rows > 0): ?>
                <div class="items-grid">
                    <?php while($report = $reports_result->fetch_assoc()): ?>
                        <div class="item-card">
                            <div class="item-image">
                                <?php if (!empty($report['img_url']) && file_exists($report['img_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($report['img_url']); ?>" 
                                         alt="Report image" 
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                <?php else: ?>
                                    🐾
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <h3 class="item-title"><?php echo htmlspecialchars($report['animal_species']); ?></h3>
                                <div class="item-meta">
                                    <span>📍 <?php echo htmlspecialchars($report['location_found']); ?></span>
                                    <span>📅 <?php echo date('M d, Y', strtotime($report['reported_at'])); ?></span>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $report['report_status'])); ?>">
                                        <?php echo htmlspecialchars($report['report_status']); ?>
                                    </span>
                                </div>
                                <?php if (!empty($report['report_type'])): ?>
                                    <div style="margin-bottom: 8px;">
                                        <strong>Type:</strong> <?php echo htmlspecialchars($report['report_type']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($report['description'])): ?>
                                    <p class="item-description"><?php echo htmlspecialchars($report['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-actions">
                                <a href="edit-report.php?id=<?php echo $report['report_id']; ?>" class="btn-action btn-edit">
                                    ✏️ Edit
                                </a>
                                <a href="report-detail.php?id=<?php echo $report['report_id']; ?>" class="btn-action btn-view">
                                    👁️ View
                                </a>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                    <input type="hidden" name="action" value="delete_report">
                                    <input type="hidden" name="id" value="<?php echo $report['report_id']; ?>">
                                    <button type="submit" class="btn-action btn-delete" style="width: 100%;">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h3>No Reports Yet</h3>
                    <p>You haven't submitted any rescue reports.</p>
                    <a href="report.php" class="btn btn-accent">Report an Animal</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Applications Tab -->
        <div id="applications-tab" class="tab-content">
            <?php if ($applications_result->num_rows > 0): ?>
                <div class="items-grid">
                    <?php while($app = $applications_result->fetch_assoc()): ?>
                        <div class="item-card">
                            <div class="item-image" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                🤝
                            </div>
                            
                            <div class="item-details">
                                <h3 class="item-title">Volunteer Application</h3>
                                <div class="item-meta">
                                    <span>📅 <?php echo date('M d, Y', strtotime($app['applied_at'])); ?></span>
                                    <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </div>
                                <?php if (!empty($app['interested'])): ?>
                                    <div style="margin-bottom: 8px;">
                                        <strong>Interests:</strong> <?php echo htmlspecialchars($app['interested']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['skills'])): ?>
                                    <p class="item-description"><?php echo htmlspecialchars($app['skills']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-actions">
                                <a href="edit-application.php?id=<?php echo $app['application_id']; ?>" class="btn-action btn-edit">
                                    ✏️ Edit
                                </a>
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this application?');">
                                    <input type="hidden" name="action" value="delete_application">
                                    <input type="hidden" name="id" value="<?php echo $app['application_id']; ?>">
                                    <button type="submit" class="btn-action btn-delete" style="width: 100%;">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🤝</div>
                    <h3>No Applications Yet</h3>
                    <p>You haven't submitted any volunteer applications.</p>
                    <a href="volunteer.php" class="btn btn-accent">Become a Volunteer</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}
</script>

<?php include 'footer.php'; ?>