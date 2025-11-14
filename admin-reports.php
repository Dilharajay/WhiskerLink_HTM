<?php 
include 'header.php'; 
include 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT * FROM admin WHERE user_id = $user_id");

if ($admin_check->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $report_id = intval($_POST['report_id']);
    $new_status = $_POST['status'];
    
    if (in_array($new_status, ['Submitted', 'Under Review', 'Resolved'])) {
        $stmt = $conn->prepare("UPDATE Rescue_Report SET report_status = ? WHERE report_id = ?");
        $stmt->bind_param("si", $new_status, $report_id);
        
        if ($stmt->execute()) {
            $success_message = "Report status has been updated successfully!";
        } else {
            $error_message = "Error updating report: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all rescue reports
$reports_result = $conn->query("SELECT rr.*, u.fullname, u.email, u.phone 
                                FROM Rescue_Report rr 
                                INNER JOIN users u ON rr.reporter_id = u.user_id 
                                ORDER BY rr.reported_at DESC");

// Get statistics
$stats = $conn->query("SELECT 
    SUM(CASE WHEN report_status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
    SUM(CASE WHEN report_status = 'Under Review' THEN 1 ELSE 0 END) as under_review,
    SUM(CASE WHEN report_status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
    COUNT(*) as total
    FROM Rescue_Report")->fetch_assoc();
?>

<style>
    .admin-container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    .page-header {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .page-header h1 {
        margin: 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .back-btn {
        padding: 10px 20px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
    }
    .back-btn:hover {
        background: #5568d3;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
    }
    .stat-number {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    .submitted { color: #ffc107; }
    .under-review { color: #17a2b8; }
    .resolved { color: #28a745; }
    
    .reports-table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .table-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 2px solid #eee;
    }
    .table-header h2 {
        margin: 0;
        color: #333;
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
        font-weight: bold;
        color: #555;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    tr:hover {
        background: #f8f9fa;
    }
    .report-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    .report-placeholder {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        border-radius: 8px;
    }
    .status-badge {
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }
    .status-submitted { background: #fff3cd; color: #856404; }
    .status-under-review { background: #cff4fc; color: #055160; }
    .status-resolved { background: #d1e7dd; color: #0f5132; }
    
    .status-select {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 13px;
        background: white;
        cursor: pointer;
    }
    .btn-update {
        background: #667eea;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        margin-top: 5px;
    }
    .btn-update:hover {
        background: #5568d3;
    }
    .btn-view {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }
    .btn-view:hover {
        background: #138496;
    }
</style>

<section id="admin-reports" style="padding: 2rem 0; background: #f5f7fa;">
    <div class="admin-container">
        <div class="page-header">
            <h1>📋 Rescue Reports Management</h1>
            <a href="admin-dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" style="padding: 1rem; margin-bottom: 1rem; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                ✓ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error" style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                ✗ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number submitted"><?php echo $stats['submitted']; ?></div>
                <div class="stat-label">Submitted</div>
            </div>
            <div class="stat-card">
                <div class="stat-number under-review"><?php echo $stats['under_review']; ?></div>
                <div class="stat-label">Under Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-number resolved"><?php echo $stats['resolved']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #667eea;"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Reports</div>
            </div>
        </div>
        
        <!-- Reports Table -->
        <div class="reports-table-container">
            <div class="table-header">
                <h2>All Rescue Reports</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Animal</th>
                        <th>Location</th>
                        <th>Reporter</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports_result->num_rows > 0): ?>
                        <?php while($report = $reports_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $report['report_id']; ?></td>
                                <td>
                                    <?php if (!empty($report['img_url']) && file_exists($report['img_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($report['img_url']); ?>" 
                                             class="report-image" 
                                             alt="Report image">
                                    <?php else: ?>
                                        <div class="report-placeholder">🐾</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($report['animal_species']); ?></strong>
                                </td>
                                <td>
                                    <div style="font-size: 13px; color: #666;">
                                        📍 <?php echo htmlspecialchars($report['location_found']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px;">
                                        <strong><?php echo htmlspecialchars($report['fullname']); ?></strong><br>
                                        <span style="color: #888;"><?php echo htmlspecialchars($report['email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: #666;">
                                        <?php echo htmlspecialchars($report['report_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $report['report_status'])); ?>">
                                        <?php echo htmlspecialchars($report['report_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 12px; color: #666;">
                                        <?php echo date('M d, Y', strtotime($report['reported_at'])); ?><br>
                                        <?php echo date('H:i', strtotime($report['reported_at'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" style="margin-bottom: 5px;">
                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <select name="status" class="status-select">
                                            <option value="Submitted" <?php echo ($report['report_status'] === 'Submitted') ? 'selected' : ''; ?>>
                                                Submitted
                                            </option>
                                            <option value="Under Review" <?php echo ($report['report_status'] === 'Under Review') ? 'selected' : ''; ?>>
                                                Under Review
                                            </option>
                                            <option value="Resolved" <?php echo ($report['report_status'] === 'Resolved') ? 'selected' : ''; ?>>
                                                Resolved
                                            </option>
                                        </select>
                                        <button type="submit" class="btn-update">Update</button>
                                    </form>
                                    <a href="report-detail.php?id=<?php echo $report['report_id']; ?>" 
                                       class="btn-view" target="_blank">
                                        👁 View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                                No rescue reports found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php 
$conn->close();
include 'footer.php'; 
?>