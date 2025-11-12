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
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
    $motivation = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Handle multiple selected interests
    $interests = isset($_POST['interests']) ? $_POST['interests'] : [];
    $interested = implode(', ', $interests); // Convert array to comma-separated string
    
    // Validate required fields
    if (empty($motivation)) {
        $error_message = "Please provide your motivation for volunteering.";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO Volunteer_Application (user_id, motivation, interested, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("iss", $user_id, $motivation, $interested);
        
        if ($stmt->execute()) {
            $success_message = "Thank you for applying! Your application has been submitted successfully and is pending review.";
        } else {
            $error_message = "Error submitting application: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<section id="volunteer-form" style="padding: 2rem 0;">
    <div class="container" style="max-width: 600px;">
        <h2 style="text-align: center;">Join Our Volunteer Team</h2>
        <p style="text-align: center;">Fill out the form below to become a part of our animal rescue community. We appreciate your support!</p>
        
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
        
        <form action="volunteer.php" method="post">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>" readonly style="background-color: #f5f5f5;">
                <small style="color: #666;">Using your registered name</small>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" readonly style="background-color: #f5f5f5;">
                <small style="color: #666;">Using your registered email</small>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="interests">Areas of Interest (select multiple) <span style="color: red;">*</span></label>
                <select id="interests" name="interests[]" multiple size="5" style="height: auto;">
                    <option value="Shelter Help">Shelter Help</option>
                    <option value="Animal Care">Animal Care</option>
                    <option value="Health">Animal Health</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Fundraising & Donations">Fundraising & Donations</option>
                </select>
                <small style="color: #666;">Hold Ctrl (or Cmd) to select multiple options</small>
            </div>
            <div class="form-group">
                <label for="message">Why do you want to volunteer? <span style="color: red;">*</span></label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-accent" style="width: 100%;">Submit Application</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>