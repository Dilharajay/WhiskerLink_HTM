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

// Handle promote to admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'promote') {
    $promote_user_id = intval($_POST['user_id']);
    
    // Check if user is already admin
    $check = $conn->query("SELECT * FROM admin WHERE user_id = $promote_user_id");
    
    if ($check->num_rows > 0) {
        $error_message = "User is already an admin!";
    } else {
        $stmt = $conn->prepare("INSERT INTO admin (user_id) VALUES (?)");
        $stmt->bind_param("i", $promote_user_id);
        
        if ($stmt->execute()) {
            $success_message = "User has been promoted to admin successfully!";
        } else {
            $error_message = "Error promoting user: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle remove admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $remove_user_id = intval($_POST['user_id']);
    
    // Prevent removing yourself
    if ($remove_user_id === $user_id) {
        $error_message = "You cannot remove yourself as admin!";
    } else {
        // Check if this is the last admin
        $admin_count = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
        
        if ($admin_count <= 1) {
            $error_message = "Cannot remove the last admin!";
        } else {
            $stmt = $conn->prepare("DELETE FROM admin WHERE user_id = ?");
            $stmt->bind_param("i", $remove_user_id);
            
            if ($stmt->execute()) {
                $success_message = "Admin privileges have been removed successfully!";
            } else {
                $error_message = "Error removing admin: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all users with admin status
$users_query = "SELECT u.*, 
                CASE WHEN a.admin_id IS NOT NULL THEN 1 ELSE 0 END as is_admin,
                (SELECT COUNT(*) FROM Volunteer_Application WHERE user_id = u.user_id) as volunteer_apps,
                (SELECT COUNT(*) FROM Rescue_Report WHERE reporter_id = u.user_id) as rescue_reports
                FROM users u
                LEFT JOIN admin a ON u.user_id = a.user_id
                ORDER BY is_admin DESC, u.user_id DESC";
$users_result = $conn->query($users_query);

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_admins = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
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
    
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        text-align: center;
    }
    .stat-number {
        font-size: 36px;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 5px;
    }
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    
    .users-table-container {
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
    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }
    .user-details {
        flex: 1;
    }
    .user-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }
    .user-email {
        font-size: 13px;
        color: #888;
    }
    .admin-badge {
        padding: 5px 12px;
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .user-badge {
        padding: 5px 12px;
        background: #e0e0e0;
        color: #666;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
    }
    .activity-count {
        text-align: center;
        font-weight: 600;
        color: #333;
    }
    .btn-promote {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
    }
    .btn-promote:hover {
        background: #218838;
    }
    .btn-remove {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
    }
    .btn-remove:hover {
        background: #c82333;
    }
    .btn-disabled {
        background: #ccc;
        cursor: not-allowed;
    }
</style>

<section id="admin-users" style="padding: 2rem 0; background: #f5f7fa;">
    <div class="admin-container">
        <div class="page-header">
            <h1>👥 User Management</h1>
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
        <div class="stats-bar">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_admins; ?></div>
                <div class="stat-label">Total Admins</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_users - $total_admins; ?></div>
                <div class="stat-label">Regular Users</div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="users-table-container">
            <div class="table-header">
                <h2>All Users</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Activity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users_result->num_rows > 0): ?>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="admin-badge">👑 Admin</span>
                                    <?php else: ?>
                                        <span class="user-badge">User</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 13px; color: #666;">
                                        📞 <?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?><br>
                                        📍 <?php echo htmlspecialchars($user['address'] ?: 'N/A'); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px; color: #666;">
                                        🤝 <?php echo $user['volunteer_apps']; ?> applications<br>
                                        📋 <?php echo $user['rescue_reports']; ?> reports
                                    </div>
                                </td>
                                <td>
                                    <?php if (!$user['is_admin']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <input type="hidden" name="action" value="promote">
                                            <button type="submit" class="btn-promote" 
                                                    onclick="return confirm('Promote <?php echo htmlspecialchars($user['fullname']); ?> to admin?')">
                                                👑 Promote to Admin
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit" 
                                                    class="btn-remove <?php echo ($user['user_id'] === $user_id) ? 'btn-disabled' : ''; ?>"
                                                    <?php echo ($user['user_id'] === $user_id) ? 'disabled' : ''; ?>
                                                    onclick="return confirm('Remove admin privileges from <?php echo htmlspecialchars($user['fullname']); ?>?')">
                                                🚫 Remove Admin
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                No users found.
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