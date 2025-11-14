<?php 
include 'header.php'; 
include 'db.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Get report ID from URL
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

if ($report_id <= 0) {
    header('Location: rescue-reports.php');
    exit;
}

// Fetch report details and reporter information
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
$stmt->close();
$conn->close();
?>

<style>
    .contact-container {
        max-width: 700px;
        margin: 2rem auto;
        padding: 0 20px;
    }
    .report-summary {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        border-left: 4px solid #ff6b6b;
    }
    .report-summary h3 {
        margin-top: 0;
        color: #333;
    }
    .report-summary-grid {
        display: grid;
        grid-template-columns: 100px auto;
        gap: 10px;
        margin-top: 15px;
    }
    .report-summary-label {
        font-weight: bold;
        color: #555;
    }
    .report-summary-value {
        color: #333;
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
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
    }
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff6b6b;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 150px;
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

<section id="contact-reporter" style="padding: 2rem 0;">
    <div class="contact-container">
        <a href="report-detail.php?id=<?php echo $report_id; ?>" class="back-button">← Back to Report</a>
        
        <h2 style="text-align: center; margin-bottom: 10px;">Contact Reporter</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Send a message to the person who reported this case</p>
        
        <div id="successAlert" class="alert alert-success">
            <span id="successMessage"></span>
            <p style="margin-top: 10px; margin-bottom: 0;">
                <a href="report-detail.php?id=<?php echo $report_id; ?>">View Report</a> | 
                <a href="rescue-reports.php">Browse More Reports</a>
            </p>
        </div>
        
        <div id="errorAlert" class="alert alert-error">
            <span id="errorMessage"></span>
        </div>
        
        <!-- Report Summary -->
        <div class="report-summary">
            <h3>About This Report</h3>
            <div class="report-summary-grid">
                <span class="report-summary-label">Animal:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['animal_species']); ?></span>
                
                <span class="report-summary-label">Location:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['location_found']); ?></span>
                
                <span class="report-summary-label">Type:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['report_type']); ?></span>
                
                <span class="report-summary-label">Reporter:</span>
                <span class="report-summary-value"><?php echo htmlspecialchars($report['reporter_name']); ?></span>
            </div>
        </div>
        
        <div class="info-box">
            <p><strong>ℹ️ Note:</strong> Your message will be sent to <strong><?php echo htmlspecialchars($report['reporter_email']); ?></strong>. They will be able to reply directly to your email address.</p>
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
                    <small style="color: #666;">The reporter will reply to this email address</small>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject <span style="color: red;">*</span></label>
                    <input type="text" id="subject" name="subject" 
                           placeholder="e.g., Interested in helping with this case" 
                           maxlength="100" required>
                    <div class="char-count">
                        <span id="subject-count">0</span>/100 characters
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message <span style="color: red;">*</span></label>
                    <textarea id="message" name="message" 
                              placeholder="Write your message here... Include any questions or offers to help." 
                              maxlength="1000" required></textarea>
                    <div class="char-count">
                        <span id="message-count">0</span>/1000 characters
                    </div>
                </div>
                
                <!-- Hidden fields for EmailJS template -->
                <input type="hidden" name="reporter_name" value="<?php echo htmlspecialchars($report['reporter_name']); ?>">
                <input type="hidden" name="reporter_email" value="<?php echo htmlspecialchars($report['reporter_email']); ?>">
                <input type="hidden" name="animal_species" value="<?php echo htmlspecialchars($report['animal_species']); ?>">
                <input type="hidden" name="location_found" value="<?php echo htmlspecialchars($report['location_found']); ?>">
                <input type="hidden" name="report_type" value="<?php echo htmlspecialchars($report['report_type']); ?>">
                <input type="hidden" name="report_id" value="<?php echo $report_id; ?>">
                <input type="hidden" name="report_link" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>/report-detail.php?id=<?php echo $report_id; ?>">
                
                <button type="submit" class="btn-accent" id="submitBtn" style="width: 100%;">
                    📧 Send Message
                </button>
            </form>
        </div>
    </div>
</section>

<!-- EmailJS SDK -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>

<!-- Optional: Load config file (if you created js/emailjs-config.js) -->
<!-- <script src="js/emailjs-config.js"></script> -->

<script>
    // EmailJS Configuration (inline version)
    const emailConfig = {
        publicKey: 'yP_HfFyjDVtRWmq6d',
        serviceId: 'service_cuwcftc',
        templateId: 'template_nn1rjgt'
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
        
        // Send email using EmailJS
        emailjs.sendForm(emailConfig.serviceId, emailConfig.templateId, this)
            .then(function(response) {
                console.log('✅ SUCCESS!', response.status, response.text);
                
                // Show success message
                document.getElementById('successMessage').textContent = 
                    'Your message has been sent successfully to the reporter! They will receive your email and can reply directly to you.';
                successAlert.style.display = 'block';
                
                // Reset form
                document.getElementById('contactForm').reset();
                subjectCount.textContent = '0';
                messageCount.textContent = '0';
                
                // Scroll to success message
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📧 Send Message';
                
            }, function(error) {
                console.error('❌ FAILED...', error);
                
                // Show error message
                document.getElementById('errorMessage').textContent = getErrorMessage(error);
                errorAlert.style.display = 'block';
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📧 Send Message';
                
                // Scroll to error message
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
    });
</script>

<?php include 'footer.php'; ?>