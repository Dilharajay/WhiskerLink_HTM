<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$filter_interest = isset($_GET['interest']) ? $_GET['interest'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build SQL query with filters
$sql = "SELECT va.*, u.fullname, u.email, u.phone, u.address 
        FROM Volunteer_Application va 
        INNER JOIN users u ON va.user_id = u.user_id 
        WHERE va.status = 'Approved'";

$params = array();
$types = "";

if ($filter_interest !== 'all') {
    $sql .= " AND va.interested LIKE ?";
    $interest_param = "%$filter_interest%";
    $params[] = $interest_param;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (u.fullname LIKE ? OR u.address LIKE ? OR va.interested LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$sql .= " ORDER BY va.applied_at DESC";

// Prepare and execute statement
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    .filter-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    .filter-btn {
        padding: 8px 16px;
        border: 2px solid #ddd;
        background: white;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        color: #333;
        font-size: 14px;
    }
    .filter-btn:hover {
        border-color: #ff6b6b;
        color: #ff6b6b;
    }
    .filter-btn.active {
        background: #ff6b6b;
        color: white;
        border-color: #ff6b6b;
    }
    .search-box {
        display: flex;
        gap: 10px;
    }
    .search-box input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .search-box button {
        padding: 10px 20px;
    }
    .volunteers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    .volunteer-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        background: white;
    }
    .volunteer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .volunteer-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        text-align: center;
    }
    .volunteer-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: white;
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        margin: 0 auto 10px;
        border: 3px solid white;
    }
    .volunteer-name {
        font-size: 20px;
        font-weight: bold;
        margin: 0;
    }
    .volunteer-content {
        padding: 20px;
    }
    .interest-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 15px 0;
    }
    .interest-tag {
        padding: 5px 12px;
        background: #e7f3ff;
        color: #0066cc;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .volunteer-info {
        margin: 15px 0;
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        color: #555;
        font-size: 14px;
    }
    .info-icon {
        width: 20px;
        text-align: center;
    }
    .volunteer-footer {
        padding: 15px 20px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
    }
    .btn-small {
        flex: 1;
        padding: 8px 16px;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        transition: all 0.3s;
    }
    .btn-view {
        background: #f0f0f0;
        color: #333;
    }
    .btn-view:hover {
        background: #e0e0e0;
    }
    .btn-contact {
        background: #ff6b6b;
        color: white;
    }
    .btn-contact:hover {
        background: #ff5252;
    }
    .no-volunteers {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .stats-bar {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-around;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .stat-item {
        text-align: center;
    }
    .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #ff6b6b;
    }
    .stat-label {
        color: #666;
        font-size: 14px;
    }
</style>

<section id="find-volunteers" style="padding: 2rem 0;">
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 10px;">Find Volunteers</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Connect with passionate volunteers ready to help animals in need</p>
        
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number"><?php echo $result->num_rows; ?></div>
                <div class="stat-label">Available Volunteers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php 
                    $interest_count = $conn->query("SELECT COUNT(DISTINCT interested) as count FROM Volunteer_Application WHERE status = 'Approved'")->fetch_assoc();
                    echo $interest_count['count'];
                    ?>
                </div>
                <div class="stat-label">Interest Areas</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php 
                    $location_count = $conn->query("SELECT COUNT(DISTINCT u.address) as count FROM Volunteer_Application va INNER JOIN users u ON va.user_id = u.user_id WHERE va.status = 'Approved'")->fetch_assoc();
                    echo $location_count['count'];
                    ?>
                </div>
                <div class="stat-label">Locations</div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <h3 style="margin-top: 0;">Filter by Interest Area</h3>
            <div class="filter-buttons">
                <a href="find-volunteers.php?interest=all<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_interest === 'all' ? 'active' : ''; ?>">
                    All Volunteers
                </a>
                <a href="find-volunteers.php?interest=Shelter Help<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_interest === 'Shelter Help' ? 'active' : ''; ?>">
                    🏠 Shelter Help
                </a>
                <a href="find-volunteers.php?interest=Animal Care<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_interest === 'Animal Care' ? 'active' : ''; ?>">
                    🐾 Animal Care
                </a>
                <a href="find-volunteers.php?interest=Health<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_interest === 'Health' ? 'active' : ''; ?>">
                    💊 Health
                </a>
                <a href="find-volunteers.php?interest=Transportation<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_interest === 'Transportation' ? 'active' : ''; ?>">
                    🚗 Transportation
                </a>
                <a href="find-volunteers.php?interest=Fundraising & Donations<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_interest === 'Fundraising & Donations' ? 'active' : ''; ?>">
                    💰 Fundraising
                </a>
            </div>
            
            <form method="GET" action="find-volunteers.php" class="search-box">
                <input type="hidden" name="interest" value="<?php echo htmlspecialchars($filter_interest); ?>">
                <input type="text" name="search" placeholder="Search by name, location, or interests..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-accent">Search</button>
            </form>
        </div>

        <!-- Volunteers Grid -->
        <div class="volunteers-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($volunteer = $result->fetch_assoc()): ?>
                    <?php 
                    // Get first letter of name for avatar
                    $initial = strtoupper(substr($volunteer['fullname'], 0, 1));
                    // Split interests into array
                    $interests = array_filter(array_map('trim', explode(',', $volunteer['interested'])));
                    ?>
                    <div class="volunteer-card">
                        <div class="volunteer-header">
                            <div class="volunteer-avatar"><?php echo $initial; ?></div>
                            <h3 class="volunteer-name"><?php echo htmlspecialchars($volunteer['fullname']); ?></h3>
                        </div>
                        
                        <div class="volunteer-content">
                            <div class="interest-tags">
                                <?php foreach($interests as $interest): ?>
                                    <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="volunteer-info">
                                <?php if (!empty($volunteer['address'])): ?>
                                    <div class="info-item">
                                        <span class="info-icon">📍</span>
                                        <span><?php echo htmlspecialchars($volunteer['address']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($volunteer['phone'])): ?>
                                    <div class="info-item">
                                        <span class="info-icon">📞</span>
                                        <span><?php echo htmlspecialchars($volunteer['phone']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <span class="info-icon">📅</span>
                                    <span>Joined <?php echo date('M Y', strtotime($volunteer['applied_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="volunteer-footer">
                            <a href="volunteer-detail.php?id=<?php echo $volunteer['application_id']; ?>" class="btn-small btn-view">
                                View Profile
                            </a>
                            <a href="contact-volunteer.php?id=<?php echo $volunteer['application_id']; ?>" class="btn-small btn-contact">
                                📧 Contact
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-volunteers" style="grid-column: 1 / -1;">
                    <h3>No volunteers found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                    <p style="margin-top: 20px;">
                        <a href="find-volunteers.php" class="btn btn-accent">View All Volunteers</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php 
$stmt->close();
$conn->close();
include 'footer.php'; 
?>