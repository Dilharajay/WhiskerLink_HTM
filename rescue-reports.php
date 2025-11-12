<?php 
include 'header.php'; 
include 'db.php';

// Get filter parameters
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build SQL query with filters
$sql = "SELECT rr.*, u.fullname as reporter_name 
        FROM Rescue_Report rr 
        LEFT JOIN users u ON rr.reporter_id = u.user_id 
        WHERE 1=1";

$params = array();
$types = "";

if ($filter_type !== 'all') {
    $sql .= " AND rr.report_type = ?";
    $params[] = $filter_type;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (rr.animal_species LIKE ? OR rr.location_found LIKE ? OR rr.description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$sql .= " ORDER BY rr.reported_at DESC";

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
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    .report-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        background: white;
    }
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .report-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f0f0f0;
    }
    .report-content {
        padding: 15px;
    }
    .report-type-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #ff6b6b;
        color: white;
        border-radius: 12px;
        font-size: 12px;
        margin-bottom: 10px;
    }
    .report-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        margin-left: 5px;
    }
    .status-submitted {
        background: #ffc107;
        color: #000;
    }
    .status-review {
        background: #17a2b8;
        color: white;
    }
    .status-resolved {
        background: #28a745;
        color: white;
    }
    .report-title {
        font-size: 18px;
        font-weight: bold;
        margin: 10px 0;
        color: #333;
    }
    .report-location {
        color: #666;
        font-size: 14px;
        margin-bottom: 8px;
    }
    .report-description {
        color: #555;
        font-size: 14px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .report-footer {
        padding: 12px 15px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #888;
    }
    .view-details-btn {
        color: #ff6b6b;
        text-decoration: none;
        font-weight: bold;
    }
    .view-details-btn:hover {
        text-decoration: underline;
    }
    .no-reports {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
</style>

<section id="rescue-reports" style="padding: 2rem 0;">
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 10px;">Rescue Reports</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Help animals in need by viewing and responding to rescue reports</p>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <h3 style="margin-top: 0;">Filter by Category</h3>
            <div class="filter-buttons">
                <a href="rescue-reports.php?type=all<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_type === 'all' ? 'active' : ''; ?>">
                    All Reports
                </a>
                <a href="rescue-reports.php?type=Shelter Help<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_type === 'Shelter Help' ? 'active' : ''; ?>">
                    Shelter Help
                </a>
                <a href="rescue-reports.php?type=Animal Care<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_type === 'Animal Care' ? 'active' : ''; ?>">
                    Animal Care
                </a>
                <a href="rescue-reports.php?type=Health<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_type === 'Health' ? 'active' : ''; ?>">
                    Health
                </a>
                <a href="rescue-reports.php?type=Transportation<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_type === 'Transportation' ? 'active' : ''; ?>">
                    Transportation
                </a>
                <a href="rescue-reports.php?type=Fundraising & Donations<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                   class="filter-btn <?php echo $filter_type === 'Fundraising & Donations' ? 'active' : ''; ?>">
                    Fundraising
                </a>
            </div>
            
            <form method="GET" action="rescue-reports.php" class="search-box">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($filter_type); ?>">
                <input type="text" name="search" placeholder="Search by animal species, location, or description..." 
                       value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-accent">Search</button>
            </form>
        </div>

        <!-- Reports Grid -->
        <div class="reports-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($report = $result->fetch_assoc()): ?>
                    <div class="report-card">
                        <?php if (!empty($report['img_url']) && file_exists($report['img_url'])): ?>
                            <img src="<?php echo htmlspecialchars($report['img_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($report['animal_species']); ?>" 
                                 class="report-image">
                        <?php else: ?>
                            <div class="report-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 48px;">
                                🐾
                            </div>
                        <?php endif; ?>
                        
                        <div class="report-content">
                            <div>
                                <span class="report-type-badge"><?php echo htmlspecialchars($report['report_type']); ?></span>
                                <span class="report-status status-<?php echo strtolower(str_replace(' ', '-', $report['report_status'])); ?>">
                                    <?php echo htmlspecialchars($report['report_status']); ?>
                                </span>
                            </div>
                            
                            <h3 class="report-title"><?php echo htmlspecialchars($report['animal_species']); ?></h3>
                            
                            <p class="report-location">
                                📍 <?php echo htmlspecialchars($report['location_found']); ?>
                            </p>
                            
                            <p class="report-description">
                                <?php echo htmlspecialchars($report['description'] ?: 'No description provided.'); ?>
                            </p>
                        </div>
                        
                        <div class="report-footer">
                            <span>Reported <?php echo date('M d, Y', strtotime($report['reported_at'])); ?></span>
                            <a href="report-detail.php?id=<?php echo $report['report_id']; ?>" class="view-details-btn">
                                View Details →
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reports" style="grid-column: 1 / -1;">
                    <h3>No reports found</h3>
                    <p>Try adjusting your filters or search terms.</p>
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