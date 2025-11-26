<?php 
include 'header.php'; 
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT * FROM admin WHERE user_id = $user_id");

if ($admin_check->num_rows === 0) {
    header('Location: index.php');
    exit;
}

// Get statistics
$stats = [];

// Total users
$stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Total admins
$stats['total_admins'] = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];

// Volunteer applications
$volunteer_stats = $conn->query("SELECT 
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
    COUNT(*) as total
    FROM Volunteer_Application")->fetch_assoc();

// Rescue reports
$report_stats = $conn->query("SELECT 
    SUM(CASE WHEN report_status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
    SUM(CASE WHEN report_status = 'Under Review' THEN 1 ELSE 0 END) as under_review,
    SUM(CASE WHEN report_status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
    COUNT(*) as total
    FROM Rescue_Report")->fetch_assoc();

// Recent activities
$recent_volunteers = $conn->query("SELECT va.*, u.fullname FROM Volunteer_Application va 
                                   INNER JOIN users u ON va.user_id = u.user_id 
                                   ORDER BY va.applied_at DESC LIMIT 5");

$recent_reports = $conn->query("SELECT rr.*, u.fullname FROM Rescue_Report rr 
                                INNER JOIN users u ON rr.reporter_id = u.user_id 
                                ORDER BY rr.reported_at DESC LIMIT 5");
?>

<style>
    .admin-dashboard {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    .dashboard-header {
        background: linear-gradient(135deg, #204B5C  0%, #204B5C 100%); 
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        text-align: center;
    }
    .dashboard-header h1 {
        margin: 0 0 10px 0;
        font-size: 36px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-left: 4px solid;
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    .stat-card.users { border-left-color: #667eea; }
    .stat-card.volunteers { border-left-color: #28a745; }
    .stat-card.reports { border-left-color: #ff6b6b; }
    .stat-card.admins { border-left-color: #ffc107; }
    
    .stat-icon {
        font-size: 36px;
        margin-bottom: 10px;
    }
    .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #333;
        margin: 10px 0;
    }
    .stat-label {
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .stat-detail {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        font-size: 13px;
        color: #888;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    .action-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
        transition: all 0.3s;
    }
    .action-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-3px);
    }
    .action-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }
    .action-card h3 {
        margin: 0 0 10px 0;
        color: #333;
    }
    .action-card p {
        color: #666;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .action-btn {
        display: inline-block;
        padding: 10px 25px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.3s;
    }
    .action-btn:hover {
        background: #5568d3;
    }
    
    .recent-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }
    .recent-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .recent-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 2px solid #eee;
    }
    .recent-header h3 {
        margin: 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .recent-list {
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .recent-item {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .recent-item:last-child {
        border-bottom: none;
    }
    .recent-item:hover {
        background: #f8f9fa;
    }
    .recent-info {
        flex: 1;
    }
    .recent-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
    }
    .recent-meta {
        font-size: 12px;
        color: #888;
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-submitted { background: #cfe2ff; color: #084298; }
    .status-under-review { background: #cff4fc; color: #055160; }
    .status-resolved { background: #d1e7dd; color: #0f5132; }
    
    @media (max-width: 968px) {
        .recent-section {
            grid-template-columns: 1fr;
        }
    }
</style>

<section id="admin-dashboard" style="padding: 2rem 0; background: #f5f7fa;">
    <div class="admin-dashboard">
<<<<<<< HEAD
        <div class="dashboard-header" >
            <h1 style="color: whitesmoke;"> Admin Dashboard</h1>
            <p style="color: whitesmoke;">Welcome back, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! Manage your platform efficiently.</p>
=======
        <div class="dashboard-header">
            <h1 style="color: #ffffffff;">🎯 Admin Dashboard</h1>
            <p style="color: #ffffffff;">Welcome back, <?php echo htmlspecialchars($_SESSION['fullname']); ?>! Manage your platform efficiently.</p>
>>>>>>> db419935268cfed7dbea713d4e5bb84ef5118fbd
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card users">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
                <div class="stat-detail">
                    <?php echo $stats['total_admins']; ?> admins
                </div>
            </div>
            
            <div class="stat-card volunteers">
                <div class="stat-icon">🤝</div>
                <div class="stat-number"><?php echo $volunteer_stats['total']; ?></div>
                <div class="stat-label">Volunteer Applications</div>
                <div class="stat-detail">
                    <?php echo $volunteer_stats['pending']; ?> pending · 
                    <?php echo $volunteer_stats['approved']; ?> approved
                </div>
            </div>
            
            <div class="stat-card reports">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?php echo $report_stats['total']; ?></div>
                <div class="stat-label">Rescue Reports</div>
                <div class="stat-detail">
                    <?php echo $report_stats['submitted']; ?> submitted · 
                    <?php echo $report_stats['resolved']; ?> resolved
                </div>
            </div>
            
            <div class="stat-card admins">
                <div class="stat-icon">👑</div>
                <div class="stat-number"><?php echo $stats['total_admins']; ?></div>
                <div class="stat-label">Total Admins</div>
                <div class="stat-detail">
                    Platform administrators
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 style="margin-bottom: 20px; color: #333;">⚡ Quick Actions</h2>
        <div class="quick-actions">
            <div class="action-card">
                <div class="action-icon">🤝</div>
                <h3>Manage Volunteers</h3>
                <p>Approve or reject volunteer applications</p>
                <a href="admin-volunteers.php" class="action-btn">Manage</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">📋</div>
                <h3>Manage Reports</h3>
                <p>Review and update rescue reports</p>
                <a href="admin-reports.php" class="action-btn">Manage</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">👥</div>
                <h3>Manage Users</h3>
                <p>View users and promote to admin</p>
                <a href="admin-users.php" class="action-btn">Manage</a>
            </div>
        </div>

        <!-- Recent Activities -->
        <h2 style="margin-bottom: 20px; color: #333;">📊 Recent Activities</h2>
        <div class="recent-section">
            <!-- Recent Volunteer Applications -->
            <div class="recent-card">
                <div class="recent-header">
                    <h3>🤝 Recent Volunteer Applications</h3>
                </div>
                <ul class="recent-list">
                    <?php if ($recent_volunteers->num_rows > 0): ?>
                        <?php while($vol = $recent_volunteers->fetch_assoc()): ?>
                            <li class="recent-item">
                                <div class="recent-info">
                                    <div class="recent-name"><?php echo htmlspecialchars($vol['fullname']); ?></div>
                                    <div class="recent-meta">
                                        <?php echo date('M d, Y H:i', strtotime($vol['applied_at'])); ?>
                                    </div>
                                </div>
                                <span class="status-badge status-<?php echo strtolower($vol['status']); ?>">
                                    <?php echo $vol['status']; ?>
                                </span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="recent-item">
                            <div class="recent-info" style="text-align: center; color: #999;">
                                No recent applications
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Recent Rescue Reports -->
            <div class="recent-card">
                <div class="recent-header">
                    <h3>📋 Recent Rescue Reports</h3>
                </div>
                <ul class="recent-list">
                    <?php if ($recent_reports->num_rows > 0): ?>
                        <?php while($rep = $recent_reports->fetch_assoc()): ?>
                            <li class="recent-item">
                                <div class="recent-info">
                                    <div class="recent-name"><?php echo htmlspecialchars($rep['animal_species']); ?></div>
                                    <div class="recent-meta">
                                        by <?php echo htmlspecialchars($rep['fullname']); ?> · 
                                        <?php echo date('M d, Y', strtotime($rep['reported_at'])); ?>
                                    </div>
                                </div>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $rep['report_status'])); ?>">
                                    <?php echo $rep['report_status']; ?>
                                </span>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="recent-item">
                            <div class="recent-info" style="text-align: center; color: #999;">
                                No recent reports
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php 
$conn->close();
include 'footer.php'; 
?>