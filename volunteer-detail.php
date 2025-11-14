<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Get volunteer ID from URL
$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($application_id <= 0) {
    header('Location: find-volunteers.php');
    exit;
}

// Fetch volunteer details
$stmt = $conn->prepare("SELECT va.*, u.fullname, u.email, u.phone, u.address 
                        FROM Volunteer_Application va 
                        INNER JOIN users u ON va.user_id = u.user_id 
                        WHERE va.application_id = ? AND va.status = 'Pending'");
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: find-volunteers.php');
    exit;
}

$volunteer = $result->fetch_assoc();
$stmt->close();
?>

<style>
    .volunteer-profile {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 12px 12px 0 0;
        text-align: center;
        position: relative;
    }
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: white;
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: bold;
        margin: 0 auto 20px;
        border: 5px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .profile-name {
        font-size: 32px;
        font-weight: bold;
        margin: 0 0 10px 0;
    }
    .profile-status {
        display: inline-block;
        padding: 8px 20px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        font-size: 14px;
    }
    .profile-content {
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }
    .profile-section {
        padding: 30px 40px;
        border-bottom: 1px solid #eee;
    }
    .profile-section:last-child {
        border-bottom: none;
    }
    .profile-section h2 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .interest-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .interest-tag {
        padding: 10px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 15px;
    }
    .info-box {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .info-icon {
        font-size: 24px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 50%;
    }
    .info-content h4 {
        margin: 0 0 5px 0;
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }
    .info-content p {
        margin: 0;
        font-size: 16px;
        color: #333;
        font-weight: 500;
    }
    .motivation-box {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
        line-height: 1.8;
        color: #555;
        font-size: 15px;
    }
    .action-buttons {
        display: flex;
        gap: 15px;
        padding: 30px 40px;
        background: #f8f9fa;
    }
    .btn-large {
        flex: 1;
        padding: 15px 30px;
        font-size: 16px;
        text-align: center;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .btn-primary {
        background: #ff6b6b;
        color: white;
    }
    .btn-primary:hover {
        background: #ff5252;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255,107,107,0.3);
    }
    .btn-secondary {
        background: white;
        color: #333;
        border: 2px solid #ddd;
    }
    .btn-secondary:hover {
        border-color: #667eea;
        color: #667eea;
    }
    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    .back-link:hover {
        text-decoration: underline;
    }
    @media(max-width: 768px) {
        .profile-section {
            padding: 20px;
        }
        .action-buttons {
            flex-direction: column;
            padding: 20px;
        }
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<section id="volunteer-detail" style="padding: 2rem 0;">
    <div class="volunteer-profile">
        <a href="find-volunteers.php" class="back-link">← Back to All Volunteers</a>
        
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($volunteer['fullname'], 0, 1)); ?>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($volunteer['fullname']); ?></h1>
            <span class="profile-status">✓ Verified Volunteer</span>
        </div>
        
        <div class="profile-content">
            <!-- Interest Areas -->
            <div class="profile-section">
                <h2>🎯 Areas of Interest</h2>
                <div class="interest-tags">
                    <?php 
                    $interests = array_filter(array_map('trim', explode(',', $volunteer['interested'])));
                    $interest_icons = [
                        'Shelter Help' => '🏠',
                        'Animal Care' => '🐾',
                        'Health' => '💊',
                        'Animal Health' => '💊',
                        'Transportation' => '🚗',
                        'Fundraising & Donations' => '💰'
                    ];
                    foreach($interests as $interest): 
                        $icon = isset($interest_icons[$interest]) ? $interest_icons[$interest] : '⭐';
                    ?>
                        <span class="interest-tag">
                            <span><?php echo $icon; ?></span>
                            <span><?php echo htmlspecialchars($interest); ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="profile-section">
                <h2>📞 Contact Information</h2>
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-icon">📧</div>
                        <div class="info-content">
                            <h4>Email Address</h4>
                            <p><?php echo htmlspecialchars($volunteer['email']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($volunteer['phone'])): ?>
                    <div class="info-box">
                        <div class="info-icon">📱</div>
                        <div class="info-content">
                            <h4>Phone Number</h4>
                            <p><?php echo htmlspecialchars($volunteer['phone']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($volunteer['address'])): ?>
                    <div class="info-box">
                        <div class="info-icon">📍</div>
                        <div class="info-content">
                            <h4>Location</h4>
                            <p><?php echo htmlspecialchars($volunteer['address']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-box">
                        <div class="info-icon">📅</div>
                        <div class="info-content">
                            <h4>Member Since</h4>
                            <p><?php echo date('F Y', strtotime($volunteer['applied_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Motivation -->
            <?php if (!empty($volunteer['motivation'])): ?>
            <div class="profile-section">
                <h2>💭 Why I Want to Volunteer</h2>
                <div class="motivation-box">
                    <?php echo nl2br(htmlspecialchars($volunteer['motivation'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="contact-volunteer.php?id=<?php echo $volunteer['application_id']; ?>" class="btn-large btn-primary">
                    <span>📧</span>
                    <span>Contact This Volunteer</span>
                </a>
                <a href="find-volunteers.php" class="btn-large btn-secondary">
                    <span>🔍</span>
                    <span>Find More Volunteers</span>
                </a>
            </div>
        </div>
    </div>
</section>

<?php 
$conn->close();
include 'footer.php'; 
?>