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

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($application_id > 0 && in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        $stmt = $conn->prepare("UPDATE Volunteer_Application SET status = ? WHERE application_id = ?");
        $stmt->bind_param("si", $new_status, $application_id);
        
        if ($stmt->execute()) {
            $success_message = "Application has been " . strtolower($new_status) . " successfully!";
        } else {
            $error_message = "Error updating application: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all volunteer applications
$result = $conn->query("SELECT va.*, u.fullname, u.email, u.phone, u.address 
                        FROM Volunteer_Application va 
                        INNER JOIN users u ON va.user_id = u.user_id 
                        ORDER BY va.applied_at DESC");
?>

<style>
    .admin-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 20px;
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
    .pending { color: #ffc107; }
    .approved { color: #28a745; }
    .rejected { color: #dc3545; }
    
    .applications-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        color: #333;
    }
    tr:hover {
        background: #f8f9fa;
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
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .btn-approve {
        background: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-approve:hover {
        background: #218838;
    }
    .btn-reject {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-reject:hover {
        background: #c82333;
    }
    .btn-view {
        background: #007bff;
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
        background: #0056b3;
    }
    .interest-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    .interest-tag {
        padding: 3px 8px;
        background: #e7f3ff;
        color: #0066cc;
        border-radius: 8px;
        font-size: 11px;
    }
</style>

<section id="admin-volunteers" style="padding: 2rem 0;">
    <div class="admin-container">
        <h1 style="text-align: center; margin-bottom: 10px;">Volunteer Management</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Approve or reject volunteer applications</p>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success" style="padding: 1rem; margin-bottom: 1rem; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error" style="padding: 1rem; margin-bottom: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <?php
            $stats = $conn->query("SELECT 
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                COUNT(*) as total
                FROM Volunteer_Application")->fetch_assoc();
            ?>
            <div class="stat-card">
                <div class="stat-number pending"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number approved"><?php echo $stats['approved']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card">
                <div class="stat-number rejected"><?php echo $stats['rejected']; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #667eea;"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        
        <!-- Applications Table -->
        <div class="applications-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Interests</th>
                        <th>Status</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($app = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $app['application_id']; ?></td>
                                <td><?php echo htmlspecialchars($app['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td>
                                    <div class="interest-tags">
                                        <?php 
                                        $interests = array_filter(array_map('trim', explode(',', $app['interested'])));
                                        foreach($interests as $interest): 
                                        ?>
                                            <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($app['status'] !== 'Approved'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn-approve" onclick="return confirm('Approve this application?')">
                                                    ✓ Approve
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($app['status'] !== 'Rejected'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="application_id" value="<?php echo $app['application_id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn-reject" onclick="return confirm('Reject this application?')">
                                                    ✗ Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($app['status'] === 'Approved'): ?>
                                            <a href="volunteer-detail.php?id=<?php echo $app['application_id']; ?>" class="btn-view" target="_blank">
                                                👁 View
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                No volunteer applications found.
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