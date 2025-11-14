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

// Fetch volunteer details - ONLY APPROVED volunteers
$stmt = $conn->prepare("SELECT va.*, u.fullname, u.email, u.phone, u.address 
                        FROM Volunteer_Application va 
                        INNER JOIN users u ON va.user_id = u.user_id 
                        WHERE va.application_id = ? AND va.status = 'Approved'");
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
    .contact-page {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 60px 0;
        min-height: calc(100vh - 140px);
    }
    
    .contact-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        font-size: 42px;
        color: #333;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }
    
    .page-header p {
        color: #666;
        font-size: 16px;
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        padding: 10px 15px;
        border-radius: 8px;
        transition: all 0.3s;
    }
    
    .back-button:hover {
        background: #667eea15;
        transform: translateX(-5px);
    }
    
    .volunteer-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 35px;
        border-radius: 20px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }
    
    .volunteer-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: white;
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: bold;
        margin: 0 auto 15px;
        border: 4px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .volunteer-summary h2 {
        margin: 0 0 10px 0;
        font-size: 32px;
    }
    
    .volunteer-summary p {
        margin: 5px 0;
        opacity: 0.95;
    }
    
    .interest-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-top: 20px;
    }
    
    .interest-badge {
        padding: 8px 16px;
        background: rgba(255,255,255,0.25);
        border-radius: 20px;
        font-size: 13px;
        backdrop-filter: blur(10px);
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
        box-sizing: border-box;
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
        min-height: 180px;
        line-height: 1.6;
    }
    
    .form-group small {
        display: block;
        margin-top: 6px;
        color: #888;
        font-size: 13px;
    }
    
    .readonly-field {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    
    .required {
        color: #ff6b6b;
        margin-left: 3px;
    }
    
    .char-count {
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    
    .char-count.warning {
        color: #ff6b6b;
        font-weight: bold;
    }
    
    .info-box {
        background: linear-gradient(135deg, #e7f3ff 0%, #d1ecf1 100%);
        padding: 18px;
        border-radius: 12px;
        margin-bottom: 25px;
        border-left: 4px solid #2196F3;
        font-size: 14px;
        color: #0d47a1;
    }
    
    .info-box p {
        margin: 5px 0;
    }
    
    .tip-box {
        background: #fff9e6;
        padding: 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        border-left: 4px solid #ffc107;
    }
    
    .tip-box h4 {
        margin: 0 0 10px 0;
        color: #856404;
        font-size: 15px;
    }
    
    .tip-box ul {
        margin: 0;
        padding-left: 20px;
        color: #856404;
        font-size: 13px;
    }
    
    .tip-box li {
        margin-bottom: 5px;
    }
    
    .submit-section {
        margin-top: 35px;
        text-align: center;
    }
    
    .submit-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 16px 50px;
        font-size: 18px;
        font-weight: 600;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
    }
    
    .submit-btn:active {
        transform: translateY(-1px);
    }
    
    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid white;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .alert {
        padding: 18px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: none;
        animation: slideDown 0.3s ease;
    }
    
    .alert-success {
        background: #d4edda;
        border: 2px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 2px solid #f5c6cb;
        color: #721c24;
    }
    
    .alert strong {
        font-size: 16px;
    }
    
    .alert a {
        color: inherit;
        font-weight: bold;
        text-decoration: underline;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @media (max-width: 768px) {
        .contact-page {
            padding: 30px 0;
        }
        
        .page-header h1 {
            font-size: 32px;
        }
        
        .form-card {
            padding: 25px;
        }
        
        .form-section {
            padding: 20px;
        }
        
        .volunteer-summary {
            padding: 25px;
        }
        
        .submit-btn {
            padding: 14px 40px;
            font-size: 16px;
        }
    }
</style>

<section class="contact-page">
    <div class="contact-container">
        <a href="volunteer-detail.php?id=<?php echo $application_id; ?>" class="back-button">
            ← Back to Volunteer Profile
        </a>
        
        <div class="page-header">
            <h1><span>📧</span> Contact Volunteer</h1>
            <p>Send a message to connect with this volunteer</p>
        </div>
        
        <div class="form-card">
            <!-- Success Alert -->
            <div id="successAlert" class="alert alert-success">
                <div>
                    <strong>✅ Message Sent Successfully!</strong><br>
                    <span id="successMessage"></span>
                    <p style="margin-top: 15px; margin-bottom: 0;">
                        <a href="volunteer-detail.php?id=<?php echo $application_id; ?>">View Profile</a> | 
                        <a href="find-volunteers.php">Find More Volunteers</a>
                    </p>
                </div>
            </div>
            
            <!-- Error Alert -->
            <div id="errorAlert" class="alert alert-error">
                <div>
                    <strong>❌ Failed to Send Message</strong><br>
                    <span id="errorMessage"></span>
                </div>
            </div>
            
            <!-- Volunteer Summary -->
            <div class="volunteer-summary">
                <div class="volunteer-avatar">
                    <?php echo strtoupper(substr($volunteer['fullname'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($volunteer['fullname']); ?></h2>
                <p>✓ Verified Volunteer</p>
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
                <p><strong>📬 Your message will be sent to:</strong> <?php echo htmlspecialchars($volunteer['email']); ?></p>
                <p>The volunteer will be able to reply directly to your email address <strong><?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?></strong></p>
            </div>
            
            <div class="tip-box">
                <h4>💡 Tips for Better Responses:</h4>
                <ul>
                    <li>Be clear about what kind of help you need</li>
                    <li>Provide specific details (location, timing, urgency)</li>
                    <li>Mention the volunteer's relevant skills</li>
                    <li>Include your availability for coordination</li>
                </ul>
            </div>
            
            <!-- Contact Form -->
            <form id="contactForm">
                
                <!-- Sender Information Section -->
                <div class="form-section">
                    <h3>👤 Your Information</h3>
                    
                    <div class="form-group">
                        <label for="sender-name">Your Name</label>
                        <input type="text" id="sender-name" name="sender_name" 
                               value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>" 
                               readonly class="readonly-field">
                    </div>
                    
                    <div class="form-group">
                        <label for="sender-email">Your Email</label>
                        <input type="email" id="sender-email" name="sender_email" 
                               value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" 
                               readonly class="readonly-field">
                        <small>The volunteer will reply to this email address</small>
                    </div>
                </div>
                
                <!-- Message Details Section -->
                <div class="form-section">
                    <h3>💬 Message Details</h3>
                    
                    <div class="form-group">
                        <label for="help_type">Type of Help Needed</label>
                        <select id="help_type" name="help_type">
                            <option value="">-- Select help type --</option>
                            <option value="Shelter Help">🏠 Shelter Help</option>
                            <option value="Animal Care">🐾 Animal Care</option>
                            <option value="Health">💊 Animal Health</option>
                            <option value="Transportation">🚗 Transportation</option>
                            <option value="Fundraising & Donations">💰 Fundraising & Donations</option>
                            <option value="Emergency Rescue">🚨 Emergency Rescue</option>
                            <option value="Fostering">🏡 Fostering</option>
                            <option value="Event Support">🎉 Event Support</option>
                            <option value="Other">💬 Other</option>
                        </select>
                        <small>Select the type of assistance you're looking for</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" 
                               placeholder="e.g., Need help with animal rescue in downtown area" 
                               maxlength="100" required>
                        <div class="char-count">
                            <span id="subject-count">0</span>/100 characters
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message <span class="required">*</span></label>
                        <textarea id="message" name="message" 
                                  placeholder="Describe what kind of help you need...

Include:
• Specific situation or task
• Location and timing
• Any special requirements
• Your contact availability"
                                  maxlength="1500" required></textarea>
                        <div class="char-count">
                            <span id="message-count">0</span>/1500 characters
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="urgency">Urgency Level</label>
                        <select id="urgency" name="urgency">
                            <option value="Normal">⏱️ Normal - Response within 24-48 hours</option>
                            <option value="Urgent">⚠️ Urgent - Need response today</option>
                            <option value="Emergency">🚨 Emergency - Immediate assistance needed</option>
                        </select>
                    </div>
                </div>
                
                <!-- Hidden fields for EmailJS template -->
                <input type="hidden" name="volunteer_name" value="<?php echo htmlspecialchars($volunteer['fullname']); ?>">
                <input type="hidden" name="volunteer_email" value="<?php echo htmlspecialchars($volunteer['email']); ?>">
                <input type="hidden" name="interested_areas" value="<?php echo htmlspecialchars($volunteer['interested']); ?>">
                <input type="hidden" name="volunteer_profile_link" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>/volunteer-detail.php?id=<?php echo $application_id; ?>">
                <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                
                <!-- Submit Section -->
                <div class="submit-section">
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span id="btnIcon">📧</span>
                        <span id="btnText">Send Message to Volunteer</span>
                    </button>
                    <p style="margin-top: 15px; color: #888; font-size: 14px;">
                        Your message will be delivered to the volunteer's email
                    </p>
                </div>
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
        templateId: 'template_volunteer' // You'll need to create this template in EmailJS
    };
    
    // Initialize EmailJS
    emailjs.init(emailConfig.publicKey);
    
    // Character counters
    const subjectInput = document.getElementById('subject');
    const subjectCount = document.getElementById('subject-count');
    const messageInput = document.getElementById('message');
    const messageCount = document.getElementById('message-count');
    
    subjectInput.addEventListener('input', function() {
        const count = this.value.length;
        subjectCount.textContent = count;
        subjectCount.parentElement.classList.toggle('warning', count > 90);
    });
    
    messageInput.addEventListener('input', function() {
        const count = this.value.length;
        messageCount.textContent = count;
        messageCount.parentElement.classList.toggle('warning', count > 1400);
    });
    
    // Auto-update subject based on help type
    document.getElementById('help_type').addEventListener('change', function() {
        if (!subjectInput.value && this.value) {
            const helpType = this.options[this.selectedIndex].text.replace(/[🏠🐾💊🚗💰🚨🏡🎉💬]/g, '').trim();
            subjectInput.value = 'Need assistance with: ' + helpType;
            subjectCount.textContent = subjectInput.value.length;
        }
    });
    
    // Helper function for error messages
    function getErrorMessage(error) {
        if (!error) return 'Unknown error occurred. Please try again.';
        
        const errorText = error.text || error.message || '';
        
        if (errorText.includes('insufficient authentication scopes')) {
            return 'Email service authentication error. Please contact the administrator.';
        } else if (errorText.includes('Invalid API key')) {
            return 'Email service configuration error. Please contact support.';
        } else if (errorText.includes('Template not found')) {
            return 'Email template error. Please contact support.';
        } else if (errorText.includes('Rate limit')) {
            return 'Too many emails sent. Please wait a few minutes and try again.';
        } else if (errorText.includes('Network')) {
            return 'Network error. Please check your internet connection and try again.';
        } else if (errorText) {
            return errorText;
        } else {
            return 'Failed to send message. Please try again or contact support.';
        }
    }
    
    // Form submission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        const btnIcon = document.getElementById('btnIcon');
        const btnText = document.getElementById('btnText');
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        
        // Disable button and show loading
        submitBtn.disabled = true;
        btnIcon.innerHTML = '<span class="spinner"></span>';
        btnText.textContent = 'Sending your message...';
        
        // Hide alerts
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';
        
        // Send email
        emailjs.sendForm(emailConfig.serviceId, emailConfig.templateId, this)
            .then(function(response) {
                console.log('✅ SUCCESS!', response.status, response.text);
                
                // Get volunteer name
                const volunteerName = document.querySelector('input[name="volunteer_name"]').value;
                
                // Success message
                document.getElementById('successMessage').innerHTML = 
                    'Your message has been sent to <strong>' + volunteerName + '</strong>. ' +
                    'They will receive your email and can reply directly to you.';
                successAlert.style.display = 'block';
                
                // Reset form
                document.getElementById('contactForm').reset();
                subjectCount.textContent = '0';
                messageCount.textContent = '0';
                
                // Scroll to success
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Re-enable button
                submitBtn.disabled = false;
                btnIcon.textContent = '📧';
                btnText.textContent = 'Send Message to Volunteer';
                
            }, function(error) {
                console.error('❌ FAILED', error);
                
                // Error message
                document.getElementById('errorMessage').textContent = getErrorMessage(error);
                errorAlert.style.display = 'block';
                
                // Re-enable button
                submitBtn.disabled = false;
                btnIcon.textContent = '📧';
                btnText.textContent = 'Send Message to Volunteer';
                
                // Scroll to error
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
    });
</script>

<?php include 'footer.php'; ?>