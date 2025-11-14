<?php 
include 'header.php'; 
include 'db.php';

// Get report ID from URL
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($report_id <= 0) {
    header('Location: rescue-reports.php');
    exit;
}

// Fetch report details
$stmt = $conn->prepare("SELECT rr.*, u.fullname as reporter_name, u.email as reporter_email 
                        FROM Rescue_Report rr 
                        LEFT JOIN users u ON rr.reporter_id = u.user_id 
                        WHERE rr.report_id = ?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: rescue-reports.php');
    exit;
}

$report = $result->fetch_assoc();
?>

<style>
    .report-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 2rem;
        align-items: start;
    }
    .report-photo img {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .report-photo .no-image {
        width: 100%;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 100px;
        border-radius: 8px;
    }
    .report-info h2 {
        margin-top: 0;
        color: #333;
    }
    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .status-submitted {
        background: #ffc107;
        color: #000;
    }
    .status-under-review {
        background: #17a2b8;
        color: white;
    }
    .status-resolved {
        background: #28a745;
        color: white;
    }
    .type-badge {
        display: inline-block;
        padding: 8px 16px;
        background: #ff6b6b;
        color: white;
        border-radius: 20px;
        font-size: 14px;
        font-weight: bold;
        margin-left: 10px;
    }
    .info-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    .info-section h3 {
        margin-top: 0;
        color: #333;
        border-bottom: 2px solid #ff6b6b;
        padding-bottom: 10px;
    }
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e0e0e0;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: bold;
        width: 180px;
        color: #555;
    }
    .info-value {
        flex: 1;
        color: #333;
    }
    .description-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #ff6b6b;
        margin: 20px 0;
        line-height: 1.8;
    }
    .back-button {
        display: inline-block;
        margin-bottom: 20px;
        color: #ff6b6b;
        text-decoration: none;
        font-weight: bold;
    }
    .back-button:hover {
        text-decoration: underline;
    }
    .contact-section {
        background: #fff3cd;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        border-left: 4px solid #ffc107;
    }
    .contact-section h3 {
        margin-top: 0;
        color: #856404;
    }
    @media(max-width: 768px) {
        .report-detail-grid {
            grid-template-columns: 1fr;
        }
        .info-row {
            flex-direction: column;
        }
        .info-label {
            width: 100%;
            margin-bottom: 5px;
        }
    }
</style>

<section id="report-detail" style="padding: 2rem 0;">
    <div class="container">
        <a href="rescue-reports.php" class="back-button">← Back to All Reports</a>
        
        <div class="report-detail-grid">
            <div class="report-photo">
                <?php if (!empty($report['img_url']) && file_exists($report['img_url'])): ?>
                    <img src="<?php echo htmlspecialchars($report['img_url']); ?>" 
                         alt="<?php echo htmlspecialchars($report['animal_species']); ?>">
                <?php else: ?>
                    <div class="no-image">🐾</div>
                <?php endif; ?>
            </div>
            
            <div class="report-info">
                <h2><?php echo htmlspecialchars($report['animal_species']); ?></h2>
                
                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $report['report_status'])); ?>">
                    <?php echo htmlspecialchars($report['report_status']); ?>
                </span>
                <span class="type-badge">
                    <?php echo htmlspecialchars($report['report_type']); ?>
                </span>
                
                <div class="info-section">
                    <h3>Report Information</h3>
                    <div class="info-row">
                        <span class="info-label">Animal Species:</span>
                        <span class="info-value"><?php echo htmlspecialchars($report['animal_species']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Location Found:</span>
                        <span class="info-value">📍 <?php echo htmlspecialchars($report['location_found']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Report Type:</span>
                        <span class="info-value"><?php echo htmlspecialchars($report['report_type']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Reported Date:</span>
                        <span class="info-value"><?php echo date('F d, Y \a\t h:i A', strtotime($report['reported_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Report Status:</span>
                        <span class="info-value"><?php echo htmlspecialchars($report['report_status']); ?></span>
                    </div>
                </div>
                
                <?php if (!empty($report['description'])): ?>
                    <h3>Description</h3>
                    <div class="description-box">
                        <?php echo nl2br(htmlspecialchars($report['description'])); ?>
                    </div>
                <?php endif; ?>
                
                <div class="contact-section">
                    <h3>Emergency Contact Information</h3>
                    <?php if (!empty($report['emergency_name'])): ?>
                        <p><strong>Contact Name:</strong> <?php echo htmlspecialchars($report['emergency_name']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($report['emegency_no'])): ?>
                        <p><strong>Contact Info:</strong> <?php echo htmlspecialchars($report['emegency_no']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($report['reporter_name'])): ?>
                        <p><strong>Reporter:</strong> <?php echo htmlspecialchars($report['reporter_name']); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['loggedin'])): ?>
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <a href="contact-reporter.php?report_id=<?php echo $report['report_id']; ?>" class="btn btn-accent" style="display: inline-block;">
                            📧 Contact Reporter
                        </a>
                    </div>
                <?php else: ?>
                    <p style="margin-top: 20px;"><a href="login.php">Log in</a> to contact the reporter about this case.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php 
$stmt->close();
$conn->close();
include 'footer.php'; 
?>