<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($application_id <= 0) {
    header('Location: my-submissions.php');
    exit;
}

// Fetch application details
$stmt = $conn->prepare("SELECT * FROM Volunteer_Application WHERE application_id = ? AND user_id = ?");
$stmt->bind_param("ii", $application_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: my-submissions.php');
    exit;
}

$application = $result->fetch_assoc();
$stmt->close();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone_no = isset($_POST['phone_no']) ? trim($_POST['phone_no']) : '';
    $skills = isset($_POST['skills']) ? trim($_POST['skills']) : '';
    
    // Handle multiple selected interests
    $interests = isset($_POST['interests']) ? $_POST['interests'] : [];
    $interested = implode(', ', $interests);
    
    // Validate required fields
    if (empty($interested)) {
        $error_message = "Please select at least one area of interest.";
    } elseif (empty($email)) {
        $error_message = "Please provide your email address.";
    } elseif (empty($skills)) {
        $error_message = "Please describe your skills and experience.";
    } else {
        $stmt = $conn->prepare("UPDATE Volunteer_Application SET interested = ?, skills = ?, email = ?, phone_no = ? WHERE application_id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $interested, $skills, $email, $phone_no, $application_id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Application updated successfully!";
            // Refresh application data
            $application['interested'] = $interested;
            $application['skills'] = $skills;
            $application['email'] = $email;
            $application['phone_no'] = $phone_no;
        } else {
            $error_message = "Error updating application: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();

// Get current interests as array
$current_interests = array_map('trim', explode(',', $application['interested']));
?>

<style>
    .volunteer-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 60px 0;
        min-height: calc(100vh - 140px);
    }
    
    .volunteer-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    
    .form-section {
        margin-bottom: 35px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 12px;
        border-left: 4px solid #667eea;
    }
    
    .form-section h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 15px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 14px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        transition: all 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 120px;
        line-height: 1.6;
    }
    
    .form-group select[multiple] {
        min-height: 180px;
        padding: 10px;
    }
    
    .form-group select[multiple] option {
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 5px;
    }
    
    .form-group select[multiple] option:checked {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .form-group small {
        display: block;
        margin-top: 6px;
        color: #888;
        font-size: 13px;
    }
    
    .required {
        color: #ff6b6b;
        margin-left: 3px;
    }
    
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
        
        .button-group {
            flex-direction: column;
        }
    }
</style>

<section class="volunteer-page">
    <div class="volunteer-container">
        <div style="margin-bottom: 20px;">
            <a href="my-submissions.php" style="color: #667eea; text-decoration: none; font-weight: 600;">← Back to My Submissions</a>
        </div>
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 36px; color: #333; margin-bottom: 10px;">
                <span>🤝</span> Edit Volunteer Application
            </h1>
            <p style="font-size: 16px; color: #666;">Update your volunteer application information</p>
        </div>
        
        <div class="form-card">
            <?php if ($success_message): ?>
                <div class="alert alert-success" style="padding: 16px 20px; border-radius: 12px; margin-bottom: 25px; background: #d4edda; border: 2px solid #c3e6cb; color: #155724;">
                    <span style="font-size: 20px;">✓</span>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error" style="padding: 16px 20px; border-radius: 12px; margin-bottom: 25px; background: #f8d7da; border: 2px solid #f5c6cb; color: #721c24;">
                    <span style="font-size: 20px;">✗</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>
            
            <form action="edit-application.php?id=<?php echo $application_id; ?>" method="post">
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3>👤 Contact Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>" 
                               readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                        <small>This is your registered name and cannot be changed here.</small>
                    </div>
                    
                    <div class="contact-grid">
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($application['email']); ?>" 
                                   required placeholder="your@email.com">
                            <small>We'll use this email to contact you</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone_no">Phone Number</label>
                            <input type="tel" id="phone_no" name="phone_no" 
                                   value="<?php echo htmlspecialchars($application['phone_no']); ?>" 
                                   placeholder="0771234567" maxlength="10">
                            <small>Optional: 10-digit phone number</small>
                        </div>
                    </div>
                </div>
                
                <!-- Interests Section -->
                <div class="form-section">
                    <h3>🎯 Areas of Interest</h3>
                    
                    <div class="form-group">
                        <label for="interests">What would you like to help with? <span class="required">*</span></label>
                        <select id="interests" name="interests[]" multiple size="6" required>
                            <option value="Shelter Help" <?php echo in_array('Shelter Help', $current_interests) ? 'selected' : ''; ?>>🏠 Shelter Help - Cleaning and maintenance</option>
                            <option value="Animal Care" <?php echo in_array('Animal Care', $current_interests) ? 'selected' : ''; ?>>🐾 Animal Care - Feeding and grooming</option>
                            <option value="Health" <?php echo in_array('Health', $current_interests) ? 'selected' : ''; ?>>💊 Animal Health - Medical assistance</option>
                            <option value="Transportation" <?php echo in_array('Transportation', $current_interests) ? 'selected' : ''; ?>>🚗 Transportation - Animal transport</option>
                            <option value="Fundraising & Donations" <?php echo in_array('Fundraising & Donations', $current_interests) ? 'selected' : ''; ?>>💰 Fundraising & Donations</option>
                            <option value="Foster Care" <?php echo in_array('Foster Care', $current_interests) ? 'selected' : ''; ?>>🏡 Foster Care - Temporary housing</option>
                        </select>
                        <small>Hold Ctrl (Windows) or Cmd (Mac) to select multiple options</small>
                    </div>
                </div>
                
                <!-- Skills & Experience Section -->
                <div class="form-section">
                    <h3>✨ Skills & Experience</h3>
                    
                    <div class="form-group">
                        <label for="skills">Tell us about your skills and experience <span class="required">*</span></label>
                        <textarea id="skills" name="skills" required 
                                  placeholder="Please describe any relevant experience, skills, or training you have..."><?php echo htmlspecialchars($application['skills']); ?></textarea>
                        <small>Help us understand how you can contribute to our mission</small>
                    </div>
                </div>
                
                <!-- Submit Section -->
                <div class="button-group">
                    <button type="submit" class="btn btn-accent" style="flex: 1; padding: 16px; font-size: 16px; font-weight: 600; border-radius: 50px;">
                        💾 Update Application
                    </button>
                    <a href="my-submissions.php" class="btn btn-secondary" style="flex: 1; padding: 16px; font-size: 16px; font-weight: 600; border-radius: 50px; text-align: center; text-decoration: none;">
                        ❌ Cancel
                    </a>
                </div>
                
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>