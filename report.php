<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $reporter_id = $_SESSION['user_id']; // Assuming user_id is stored in session
    $report_type = isset($_POST['report-type']) ? trim($_POST['report-type']) : '';
    $animal_species = isset($_POST['animal-type']) ? trim($_POST['animal-type']) : '';
    $location_found = isset($_POST['location']) ? trim($_POST['location']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $emergency_name = isset($_POST['contact-name']) ? trim($_POST['contact-name']) : '';
    $emergency_no = isset($_POST['contact-email']) ? trim($_POST['contact-email']) : '';
    
    // Handle file upload
    $img_url = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/rescue_reports/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get file extension
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Generate unique filename
            $new_filename = 'rescue_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $img_url = $target_file;
            } else {
                $error_message = "Error uploading file.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }
    
    // Validate required fields
    if (empty($animal_species) || empty($location_found) || empty($emergency_name)) {
        $error_message = "Please fill in all required fields.";
    } elseif (empty($error_message)) {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO Rescue_Report (reporter_id, report_type, report_status, animal_species, location_found, description, img_url, emergency_name, emegency_no) VALUES (?, ?, 'Submitted', ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $reporter_id, $report_type, $animal_species, $location_found, $description, $img_url, $emergency_name, $emergency_no);
        
        if ($stmt->execute()) {
            $success_message = "Thank you! Your rescue report has been submitted successfully. Our team will review it shortly.";
            // Clear form by redirecting
            // header('Location: report.php?success=1');
            // exit;
        } else {
            $error_message = "Error submitting report: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<section id="report-form" style="padding: 2rem 0;">
    <div class="container" style="max-width: 600px;">
        <h2 style="text-align: center;">Report a Rescued or Lost Animal</h2>
        <p style="text-align: center;">Thank you for helping an animal in need. Please provide as much detail as possible.</p>
        
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
        
        <form action="report.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="report-type">Report Type <span style="color: red;">*</span></label>
                <select id="report-type" name="report-type" required>
                    <option value="">Select a type</option>
                    <option value="Shelter Help">Shelter Help</option>
                    <option value="Animal Care">Animal Care</option>
                    <option value="Health">Animal Health</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Fundraising & Donations">Fundraising & Donations</option>
                </select>
            </div>
            <div class="form-group">
                <label for="animal-type">Type of Animal <span style="color: red;">*</span></label>
                <input type="text" id="animal-type" name="animal-type" placeholder="e.g., Dog, Cat, Bird" required>
            </div>
            <div class="form-group">
                <label for="location">Location Found/Lost <span style="color: red;">*</span></label>
                <input type="text" id="location" name="location" placeholder="City, State, or specific address" required>
            </div>
            <div class="form-group">
                <label for="description">Description of Animal</label>
                <textarea id="description" name="description" rows="5" placeholder="Include breed, color, size, and any distinguishing marks."></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Upload a Photo</label>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif">
                <small style="color: #666;">Allowed formats: JPG, JPEG, PNG, GIF (Max 5MB)</small>
            </div>
            <div class="form-group">
                <label for="contact-name">Your Name <span style="color: red;">*</span></label>
                <input type="text" id="contact-name" name="contact-name" value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="contact-email">Your Email/Phone <span style="color: red;">*</span></label>
                <input type="text" id="contact-email" name="contact-email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
                <small style="color: #666;">We'll use this to contact you about the report</small>
            </div>
            <button type="submit" class="btn btn-accent" style="width: 100%;">Submit Report</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>