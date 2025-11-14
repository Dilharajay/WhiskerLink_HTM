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
$conn->close();
?>

<style>
    .contact-container {
        max-width: 700px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    .volunteer-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
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
        font-weight: bold;
        margin: 0 auto 15px;
        border: 3px solid white;
    }
    .volunteer-summary h2 {
        margin: 0 0 10px 0;
        font-size: 28px;
    }
    .interest-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-top: 15px;
    }
    .interest-badge {
        padding: 5px 12px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        font-size: 12px;
    }
    .form-container {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 150px;
    }
    .back-button {
        display: inline-block;
        margin-bottom: 20px;
        color: #667eea;
        text-decoration: none;
        font-weight: bold;
    }
    .back-button:hover {
        text-decoration: underline;
    }
    .char-count {
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    .info-box {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border-left: 4px solid #2196F3;
    }
    .info-box p {
        margin: 0;
        color: #0d47a1;
    }
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 4px;
        display: none;
    }
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    .alert-error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .btn-accent {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn-accent:hover {
        transform: translateY(-2px);
    }
    .btn-accent:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
        margin-right: 8px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<section id="contact-volunteer" style="padding: 2rem 0;">
    <div class="contact-container">
        <a href="volunteer-detail.php?id=<?php echo $application_id; ?>" class="back-button">← Back to Volunteer Profile</a>
        
        <h1 style="text-align: center; margin-bottom: 30px;">Contact Volunteer</h1>
        
        <div id="successAlert" class="alert alert-success">
            <span id="successMessage"></span>
            <p style="margin-top: 10px; margin-bottom: 0;">
                <a href="volunteer-detail.php?id=<?php echo $application_id; ?>">View Profile</a> | 
                <a href="find-volunteers.php">Find More Volunteers</a>
            </p>
        </div>
        
        <div id="errorAlert" class="alert alert-error">
            <span id="errorMessage"></span>
        </div>
        
        <!-- Volunteer Summary -->
        <div class="volunteer-summary">
            <div class="volunteer-avatar">
                <?php echo strtoupper(substr($volunteer['fullname'], 0, 1)); ?>
            </div>
            <h2><?php echo htmlspecialchars($volunteer['fullname']); ?></h2>
            <p style="margin: 5px 0;">✓ Verified Volunteer</p>
            <div class="interest-badges">
                <?php 
                $interests = array_filter(array_map('trim', explode(',', $volunteer['interested'])));
                foreach($interests as $interest): 
                ?>
                    <span class="interest-badge"><?php echo htmlspecialchars($interest); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="info-box">
            <p><strong>ℹ️ Note:</strong> Your message will be sent to <strong><?php echo htmlspecialchars($volunteer['email']); ?></strong>. They will be able to reply directly to your email address.</p>
        </div>
        
        <!-- Contact Form -->
        <div class="form-container">
            <form id="contactForm">
                <div class="form-group">
                    <label for="sender-name">Your Name</label>
                    <input type="text" id="sender-name" name="sender_name" 
                           value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>" 
                           readonly style="background-color: #f5f5f5;">
                </div>
                
                <div class="form-group">
                    <label for="sender-email">Your Email</label>
                    <input type="email" id="sender-email" name="sender_email" 
                           value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" 
                           readonly style="background-color: #f5f5f5;">
                    <small style="color: #666;">The volunteer will reply to this email address</small>
                </div>
                
                <div class="form-group">
                    <label for="help_type">Type of Help Needed</label>
                    <select id="help_type" name="help_type">
                        <option value="">Select help type (optional)</option>
                        <option value="Shelter Help">Shelter Help</option>
                        <option value="Animal Care">Animal Care</option>
                        <option value="Health">Animal Health</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Fundraising & Donations">Fundraising & Donations</option>
                        <option value="Emergency Rescue">Emergency Rescue</option>
                        <option value="Fostering">Fostering</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject <span style="color: red;">*</span></label>
                    <input type="text" id="subject" name="subject" 
                           placeholder="e.g., Need help with animal rescue" 
                           maxlength="100" required>
                    <div class="char-count">
                        <span id="subject-count">0</span>/100 characters
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message <span style="color: red;">*</span></label>
                    <textarea id="message" name="message" 
                              placeholder="Describe what kind of help you need, provide details about the situation, location, timing, etc." 
                              maxlength="1000" required></textarea>
                    <div class="char-count">
                        <span id="message-count">0</span>/1000 characters
                    </div>
                </div>
                
                <!-- Hidden fields for EmailJS template -->
                <input type="hidden" name="volunteer_name" value="<?php echo htmlspecialchars($volunteer['fullname']); ?>">
                <input type="hidden" name="volunteer_email" value="<?php echo htmlspecialchars($volunteer['email']); ?>">
                <input type="hidden" name="interested_areas" value="<?php echo htmlspecialchars($volunteer['interested']); ?>">
                <input type="hidden" name="volunteer_profile_link" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>/volunteer-detail.php?id=<?php echo $application_id; ?>">
                
                <button type="submit" class="btn-accent" id="submitBtn" style="width: 100%;">
                    📧 Send Message to Volunteer
                </button>
            </form>
        </div>
    </div>
</section>

<!-- EmailJS SDK -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>

<script>
    // EmailJS Configuration
    const emailConfig = {
        publicKey: 'yP_HfFyjDVtRWmq6d',
        serviceId: 'service_cuwcftc',
        templateId: 'template_volunteer_contact' // You'll need to create this template
    };
    
    // Initialize EmailJS
    emailjs.init(emailConfig.publicKey);
    
    // Character counter for subject
    const subjectInput = document.getElementById('subject');
    const subjectCount = document.getElementById('subject-count');
    
    subjectInput.addEventListener('input', function() {
        subjectCount.textContent = this.value.length;
    });
    
    // Character counter for message
    const messageInput = document.getElementById('message');
    const messageCount = document.getElementById('message-count');
    
    messageInput.addEventListener('input', function() {
        messageCount.textContent = this.value.length;
    });
    
    // Helper function to get error message
    function getErrorMessage(error) {
        if (!error) return 'Unknown error occurred';
        
        const errorText = error.text || error.message || '';
        
        if (errorText.includes('insufficient authentication scopes')) {
            return 'Email service authentication error. Please contact the administrator to reconnect the email service.';
        } else if (errorText.includes('Invalid API key') || errorText.includes('public_key')) {
            return 'Email service configuration error. Please contact support.';
        } else if (errorText.includes('Template not found')) {
            return 'Email template error. Please contact support.';
        } else if (errorText.includes('Rate limit') || errorText.includes('Too Many Requests')) {
            return 'Too many emails sent. Please try again in a few minutes.';
        } else if (errorText.includes('Network') || errorText.includes('Failed to fetch')) {
            return 'Network error. Please check your internet connection and try again.';
        } else if (errorText) {
            return 'Error: ' + errorText;
        } else {
            return 'Failed to send message. Please try again or contact support.';
        }
    }
    
    // Form submission handler
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span>Sending...';
        
        // Hide previous alerts
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';
        
        // Get form data
        const formData = new FormData(this);
        const templateParams = {
            to_email: formData.get('volunteer_email'),
            volunteer_name: formData.get('volunteer_name'),
            volunteer_email: formData.get('volunteer_email'),
            sender_name: formData.get('sender_name'),
            sender_email: formData.get('sender_email'),
            help_type: formData.get('help_type') || 'Not specified',
            subject: formData.get('subject'),
            message: formData.get('message'),
            interested_areas: formData.get('interested_areas'),
            volunteer_profile_link: formData.get('volunteer_profile_link')
        };
        
        // Send email using EmailJS
        emailjs.send(emailConfig.serviceId, emailConfig.templateId, templateParams)
            .then(function(response) {
                console.log('✅ SUCCESS!', response.status, response.text);
                
                // Show success message
                document.getElementById('successMessage').textContent = 
                    'Your message has been sent successfully to ' + formData.get('volunteer_name') + '! They will receive your email and can reply directly to you.';
                successAlert.style.display = 'block';
                
                // Reset form
                document.getElementById('contactForm').reset();
                subjectCount.textContent = '0';
                messageCount.textContent = '0';
                
                // Scroll to success message
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📧 Send Message to Volunteer';
                
            }, function(error) {
                console.error('❌ FAILED...', error);
                
                // Show error message
                document.getElementById('errorMessage').textContent = getErrorMessage(error);
                errorAlert.style.display = 'block';
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📧 Send Message to Volunteer';
                
                // Scroll to error message
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
    });
</script>

<?php include 'footer.php'; ?>