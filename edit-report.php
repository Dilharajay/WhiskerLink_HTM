<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($report_id <= 0) {
    header('Location: my-submissions.php');
    exit;
}

// Fetch report details
$stmt = $conn->prepare("SELECT * FROM Rescue_Report WHERE report_id = ? AND reporter_id = ?");
$stmt->bind_param("ii", $report_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: my-submissions.php');
    exit;
}

$report = $result->fetch_assoc();
$stmt->close();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = isset($_POST['report-type']) ? trim($_POST['report-type']) : '';
    $animal_species = isset($_POST['animal-type']) ? trim($_POST['animal-type']) : '';
    $location_found = isset($_POST['location']) ? trim($_POST['location']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $emergency_name = isset($_POST['contact-name']) ? trim($_POST['contact-name']) : '';
    $emergency_phone = isset($_POST['emergency-phone']) ? trim($_POST['emergency-phone']) : '';
    $reporter_email = isset($_POST['reporter-email']) ? trim($_POST['reporter-email']) : '';
    
    // Handle file upload (optional - only if new image uploaded)
    $img_url = $report['img_url'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/rescue_reports/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'rescue_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                // Delete old image if exists
                if (!empty($report['img_url']) && file_exists($report['img_url'])) {
                    unlink($report['img_url']);
                }
                $img_url = $target_file;
            }
        }
    }
    
    // Validate required fields
    if (empty($animal_species) || empty($location_found) || empty($reporter_email)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE Rescue_Report SET report_type = ?, animal_species = ?, location_found = ?, description = ?, img_url = ?, emergency_name = ?, emegency_no = ?, reporter_email = ? WHERE report_id = ? AND reporter_id = ?");
        $stmt->bind_param("ssssssssii", $report_type, $animal_species, $location_found, $description, $img_url, $emergency_name, $emergency_phone, $reporter_email, $report_id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Report updated successfully!";
            // Refresh report data
            $report['report_type'] = $report_type;
            $report['animal_species'] = $animal_species;
            $report['location_found'] = $location_found;
            $report['description'] = $description;
            $report['img_url'] = $img_url;
            $report['emergency_name'] = $emergency_name;
            $report['emegency_no'] = $emergency_phone;
            $report['reporter_email'] = $reporter_email;
        } else {
            $error_message = "Error updating report: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<link rel="stylesheet" href="css/home.css">

<section id="report-form" style="padding: 3rem 0; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
    <div class="container" style="max-width: 700px;">
        <div style="margin-bottom: 20px;">
            <a href="my-submissions.php" style="color: #667eea; text-decoration: none; font-weight: 600;">← Back to My Submissions</a>
        </div>
        
        <h2 style="text-align: center; margin-bottom: 10px;">Edit Rescue Report</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Update your rescue report information</p>
        
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
        
        <form action="edit-report.php?id=<?php echo $report_id; ?>" method="post" enctype="multipart/form-data" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            
            <!-- Animal Information Section -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #ff6b6b;">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 18px;">🐾 Animal Information</h3>
                
                <div class="form-group">
                    <label for="report-type">Report Type <span style="color: red;">*</span></label>
                    <select id="report-type" name="report-type" required>
                        <option value="">Select a type</option>
                        <option value="Shelter Help" <?php echo ($report['report_type'] === 'Shelter Help') ? 'selected' : ''; ?>>Shelter Help</option>
                        <option value="Animal Care" <?php echo ($report['report_type'] === 'Animal Care') ? 'selected' : ''; ?>>Animal Care</option>
                        <option value="Health" <?php echo ($report['report_type'] === 'Health') ? 'selected' : ''; ?>>Animal Health</option>
                        <option value="Transportation" <?php echo ($report['report_type'] === 'Transportation') ? 'selected' : ''; ?>>Transportation</option>
                        <option value="Fundraising & Donations" <?php echo ($report['report_type'] === 'Fundraising & Donations') ? 'selected' : ''; ?>>Fundraising & Donations</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="animal-type">Type of Animal <span style="color: red;">*</span></label>
                    <input type="text" id="animal-type" name="animal-type" placeholder="e.g., Dog, Cat, Bird" value="<?php echo htmlspecialchars($report['animal_species']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="location">Location Found/Lost <span style="color: red;">*</span></label>
                    <input type="text" id="location" name="location" placeholder="City, State, or specific address" value="<?php echo htmlspecialchars($report['location_found']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description of Animal</label>
                    <textarea id="description" name="description" rows="5" placeholder="Include breed, color, size, and any distinguishing marks."><?php echo htmlspecialchars($report['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="photo">Update Photo (optional)</label>
                    <?php if (!empty($report['img_url']) && file_exists($report['img_url'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?php echo htmlspecialchars($report['img_url']); ?>" alt="Current image" style="max-width: 200px; border-radius: 8px;">
                            <p style="font-size: 13px; color: #666; margin-top: 5px;">Current image (upload a new one to replace)</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif">
                    <small style="color: #666;">Allowed formats: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                </div>
            </div>
            
            <!-- Contact Information Section -->
            <div style="background: #fff3e0; padding: 20px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #ff9800;">
                <h3 style="margin: 0 0 20px 0; color: #333; font-size: 18px;">📞 Emergency Contact Information</h3>
                
                <div class="form-group">
                    <label for="contact-name">Contact Person Name <span style="color: red;">*</span></label>
                    <input type="text" id="contact-name" name="contact-name" value="<?php echo htmlspecialchars($report['emergency_name']); ?>" required placeholder="Enter contact person's name">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="emergency-phone">Phone Number</label>
                        <input type="tel" id="emergency-phone" name="emergency-phone" value="<?php echo htmlspecialchars($report['emegency_no']); ?>" placeholder="+94 77 123 4567">
                        <small style="color: #666;">Emergency contact phone</small>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="reporter-email">Email Address <span style="color: red;">*</span></label>
                        <input type="email" id="reporter-email" name="reporter-email" value="<?php echo htmlspecialchars($report['reporter_email']); ?>" required placeholder="your@email.com">
                        <small style="color: #666;">Your contact email</small>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn btn-accent" style="flex: 1; padding: 15px; font-size: 16px; font-weight: 600;">
                    💾 Update Report
                </button>
                <a href="my-submissions.php" class="btn btn-secondary" style="flex: 1; padding: 15px; font-size: 16px; font-weight: 600; text-align: center; text-decoration: none;">
                    ❌ Cancel
                </a>
            </div>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>